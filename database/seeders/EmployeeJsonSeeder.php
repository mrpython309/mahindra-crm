<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DealerLocation;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Segment;
use App\Models\SubSegment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class EmployeeJsonSeeder extends Seeder
{
    private array $designationMap = [];
    private array $companyMap = [];
    private array $companyNamesById = [];
    private array $segmentMap = [];
    private array $departmentMap = [];
    private array $subSegmentMapByName = [];
    private array $subSegmentMapBySegmentAndName = [];
    private array $dealerLocationMapByCompany = [];
    private array $dealerLocationMapByCompanyAndName = [];
    private array $roleMapByName = [];
    private array $allSegmentIds = [];
    private array $allDepartmentIds = [];
    private array $allDealerLocationIdsByCompany = [];
    private array $allSubSegmentIdsBySegment = [];
    private int $skippedCount = 0;
    private int $insertedUsersCount = 0;
    private int $insertedEmployeesCount = 0;
    private int $updatedReportingCount = 0;

    public function run(): void
    {
        $jsonPath = env('EMPLOYEE_SEED_JSON_PATH', database_path('seeders/data/employees_sample_master_first_sheet.json'));

        if (!is_file($jsonPath)) {
            $this->command?->warn("EmployeeJsonSeeder skipped. JSON not found at: {$jsonPath}");
            return;
        }

        $rows = json_decode((string) file_get_contents($jsonPath), true);
        if (!is_array($rows)) {
            $this->command?->error("EmployeeJsonSeeder failed. Invalid JSON file: {$jsonPath}");
            return;
        }

        $rowsByEmail = [];
        foreach ($rows as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email !== '') {
                $rowsByEmail[$email] = $row;
            }
        }

        $this->preloadLookupMaps();

        $this->command?->newLine();
        $this->command?->info('EmployeeJsonSeeder started...');
        $this->command?->line('Total input rows: ' . count($rowsByEmail));

        DB::transaction(function () use ($rowsByEmail) {
            $this->seedBaseEmployeeAndUserData($rowsByEmail);
            $this->seedReportingHierarchy($rowsByEmail);
        });

        $this->command?->newLine();
        $this->command?->info('EmployeeJsonSeeder completed successfully.');
        $this->command?->table(
            ['Metric', 'Count'],
            [
                ['Input rows', count($rowsByEmail)],
                ['Inserted users', $this->insertedUsersCount],
                ['Inserted employees', $this->insertedEmployeesCount],
                ['Reporting rows updated', $this->updatedReportingCount],
                ['Skipped rows', $this->skippedCount],
            ]
        );
    }

    private function seedBaseEmployeeAndUserData(array $rowsByEmail): void
    {
        $now = now();
        $userRows = [];
        $employeeRows = [];
        $userRoleByEmail = [];
        $progressBar = $this->command?->getOutput()->createProgressBar(count($rowsByEmail));
        if ($progressBar) {
            $progressBar->setFormat('verbose');
            $progressBar->setMessage('Building insert payloads');
            $progressBar->start();
        }

        foreach ($rowsByEmail as $email => $row) {
            $designationId = $this->resolveDesignationId($row['designation_name'] ?? null);
            if ($designationId === null) {
                $this->warnSkip($email, 'designation_name not mapped');
                $progressBar?->advance();
                continue;
            }

            $companyId = $this->resolveCompanyId($row['company_name'] ?? null);
            $segmentIds = $this->resolveSegmentIds($row['segment_names'] ?? []);
            $departmentIds = $this->resolveDepartmentIds($row['department_name'] ?? null);
            $subsegmentIds = $this->resolveSubSegmentIds($row['sub_segment_names'] ?? [], $segmentIds);
            $locationIds = $this->resolveLocationIds($row['location_name'] ?? null, $companyId, $row['company_name'] ?? null);

            $isUser = strtolower((string) ($row['is_user'] ?? 'no')) === 'yes' ? 'yes' : 'no';
            $roleId = $this->resolveRoleId($row['role_id'] ?? null, $row['role_name'] ?? null, $isUser);

            $employeeRows[$email] = [
                'email' => $email,
                'emp_code' => (string) ($row['emp_code'] ?? ''),
                'mile_id' => (string) ($row['mile_id'] ?? ''),
                'employee_name' => (string) ($row['employee_name'] ?? ''),
                'company_id' => $companyId ?? 0,
                'is_left' => 'no',
                'is_user' => $isUser,
                'designation_id' => $designationId,
                'segment_id' => json_encode(array_values($segmentIds)),
                'department_id' => json_encode(array_values($departmentIds)),
                'dealer_location_id' => json_encode(array_values($locationIds)),
                'subsegment_id' => json_encode(array_values($subsegmentIds)),
                'reporting_to' => json_encode([]),
                'mobile_number' => (string) ($row['mobile_number'] ?? ''),
                'date_of_joining' => (string) ($row['date_of_joining'] ?? now()->toDateString()),
                'employee_status' => (string) ($row['employee_status'] ?? 'active'),
                'left_date' => null,
                'role_id' => $isUser === 'yes' ? $roleId : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($isUser !== 'yes') {
                continue;
            }

            $userRows[] = [
                'email' => $email,
                'name' => (string) ($row['employee_name'] ?? ''),
                'password' => Hash::make((string) ($row['password'] ?? 'password')),
                'is_user' => 'yes',
                'role_id' => $roleId,
                'reporting_status' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($roleId !== null) {
                $userRoleByEmail[$email] = $roleId;
            }

            $progressBar?->advance();
        }

        if ($progressBar) {
            $progressBar->finish();
            $this->command?->newLine();
            $this->command?->line('Payload preparation complete.');
        }

        if ($userRows !== []) {
            $this->command?->line('Inserting users in chunks...');
            foreach (array_chunk($userRows, 200) as $chunk) {
                DB::table('users')->insert($chunk);
                $this->insertedUsersCount += count($chunk);
            }
        }

        $userIdByEmail = User::whereIn('email', array_keys($rowsByEmail))
            ->pluck('id', 'email')
            ->mapWithKeys(fn($id, $email) => [strtolower(trim((string) $email)) => (int) $id])
            ->all();

        foreach ($employeeRows as $email => &$employeeRow) {
            $employeeRow['user_id'] = $userIdByEmail[$email] ?? null;
        }
        unset($employeeRow);

        if ($employeeRows !== []) {
            $this->command?->line('Inserting employees in chunks...');
            foreach (array_chunk(array_values($employeeRows), 200) as $chunk) {
                DB::table('employees')->insert($chunk);
                $this->insertedEmployeesCount += count($chunk);
            }
        }

        if ($userRoleByEmail !== []) {
            $this->command?->line('Assigning roles...');
            $users = User::whereIn('email', array_keys($userRoleByEmail))->get(['id', 'email']);
            foreach ($users as $user) {
                $roleId = $userRoleByEmail[strtolower(trim($user->email))] ?? null;
                $roleName = $roleId ? ($this->roleMapByName['_id_' . $roleId] ?? null) : null;
                if ($roleName) {
                    $user->syncRoles([$roleName]);
                }
            }
        }
    }

    private function seedReportingHierarchy(array $rowsByEmail): void
    {
        $employees = Employee::query()->get(['id', 'email', 'employee_name']);
        $employeeByName = $employees->groupBy(
            fn(Employee $employee) => strtolower(trim($employee->employee_name))
        );
        $employeeByEmail = $employees->keyBy(
            fn(Employee $employee) => strtolower(trim($employee->email))
        );

        $reportingUpdates = [];
        $now = now();
        $progressBar = $this->command?->getOutput()->createProgressBar(count($rowsByEmail));
        if ($progressBar) {
            $progressBar->setFormat('verbose');
            $progressBar->setMessage('Building reporting hierarchy');
            $progressBar->start();
        }

        foreach ($rowsByEmail as $email => $row) {
            /** @var Employee|null $employee */
            $employee = $employeeByEmail->get($email);
            if (!$employee) {
                $progressBar?->advance();
                continue;
            }

            $reportingIds = [];
            $reportingNames = is_array($row['reporting_to_names'] ?? null) ? $row['reporting_to_names'] : [];

            foreach ($reportingNames as $name) {
                $key = strtolower(trim((string) $name));
                if ($key === '') {
                    continue;
                }

                $matches = $employeeByName->get($key);
                if (!$matches || $matches->isEmpty()) {
                    $this->warnSkip($email, "reporting manager not found: {$name}");
                    continue;
                }

                if ($matches->count() > 1) {
                    $this->warnSkip($email, "duplicate employee_name found for reporting manager: {$name}");
                }

                $managerId = (int) $matches->first()->id;
                if (!in_array($managerId, $reportingIds, true)) {
                    $reportingIds[] = $managerId;
                }

                if (count($reportingIds) === 3) {
                    break;
                }
            }

            $reportingUpdates[] = [
                'id' => (int) $employee->id,
                'reporting_to' => json_encode($reportingIds),
                'updated_at' => $now,
            ];
            $progressBar?->advance();
        }

        if ($progressBar) {
            $progressBar->finish();
            $this->command?->newLine();
            $this->command?->line('Reporting hierarchy preparation complete.');
        }

        $this->command?->line('Updating reporting hierarchy...');
        foreach ($reportingUpdates as $reportingUpdate) {
            DB::table('employees')
                ->where('id', (int) $reportingUpdate['id'])
                ->update([
                    'reporting_to' => $reportingUpdate['reporting_to'],
                    'updated_at' => $reportingUpdate['updated_at'],
                ]);
            $this->updatedReportingCount++;
        }
    }

    private function resolveDesignationId(?string $designationName): ?int
    {
        $designationName = strtolower(trim((string) $designationName));
        if ($designationName === '') {
            return null;
        }
        return $this->designationMap[$designationName] ?? null;
    }

    private function resolveCompanyId(?string $companyName): ?int
    {
        $companyName = strtolower(trim((string) $companyName));
        if ($companyName === '') {
            return null;
        }
        return $this->companyMap[$companyName] ?? null;
    }

    private function resolveSegmentIds(array $segmentNames): array
    {
        $segmentNames = array_values(array_filter(array_map(static fn($name) => strtolower(trim((string) $name)), $segmentNames)));
        if ($segmentNames === []) {
            return [];
        }

        if (in_array('all segment', $segmentNames, true)) {
            return $this->allSegmentIds;
        }

        $resolved = [];
        foreach ($segmentNames as $name) {
            if (isset($this->segmentMap[$name])) {
                $resolved[$this->segmentMap[$name]] = (int) $this->segmentMap[$name];
            }
        }
        return array_values($resolved);
    }

    private function resolveDepartmentIds(?string $departmentName): array
    {
        $departmentName = trim((string) $departmentName);
        if ($departmentName === '') {
            return [];
        }

        if (strtolower($departmentName) === 'all department') {
            return $this->allDepartmentIds;
        }

        $key = strtolower($departmentName);
        return isset($this->departmentMap[$key]) ? [(int) $this->departmentMap[$key]] : [];
    }

    private function resolveSubSegmentIds(array $subSegmentNames, array $segmentIds): array
    {
        $subSegmentNames = array_values(array_filter(array_map(static fn($name) => strtolower(trim((string) $name)), $subSegmentNames)));
        if ($subSegmentNames === []) {
            return [];
        }

        if (in_array('all segment', $subSegmentNames, true)) {
            if ($segmentIds === []) {
                return array_values(array_unique(array_values($this->subSegmentMapByName)));
            }

            $all = [];
            foreach ($segmentIds as $segmentId) {
                foreach (($this->allSubSegmentIdsBySegment[$segmentId] ?? []) as $subSegmentId) {
                    $all[$subSegmentId] = $subSegmentId;
                }
            }
            return array_values($all);
        }

        $resolved = [];
        foreach ($subSegmentNames as $subSegmentName) {
            if ($segmentIds !== []) {
                foreach ($segmentIds as $segmentId) {
                    $key = $segmentId . '|' . $subSegmentName;
                    if (isset($this->subSegmentMapBySegmentAndName[$key])) {
                        $id = (int) $this->subSegmentMapBySegmentAndName[$key];
                        $resolved[$id] = $id;
                    }
                }
                continue;
            }

            if (isset($this->subSegmentMapByName[$subSegmentName])) {
                $id = (int) $this->subSegmentMapByName[$subSegmentName];
                $resolved[$id] = $id;
            }
        }
        return array_values($resolved);
    }

    private function resolveLocationIds(?string $locationName, ?int $companyId, ?string $companyName): array
    {
        $locationName = trim((string) $locationName);
        if ($locationName === '') {
            return [];
        }

        $normalizedLocation = strtolower($locationName);
        if ($normalizedLocation === 'all location') {
            if ($companyId === null) {
                Log::warning('EmployeeJsonSeeder warning: company_id unresolved for "All location"', [
                    'company_name' => $companyName,
                ]);
                return [];
            }

            $ids = $this->allDealerLocationIdsByCompany[$companyId] ?? [];
            if ($ids === []) {
                Log::warning('EmployeeJsonSeeder warning: no dealer locations found for company', [
                    'company_id' => $companyId,
                    'company_name' => $this->companyNamesById[$companyId] ?? $companyName,
                ]);
            }

            return $ids;
        }

        if ($companyId !== null) {
            $key = $companyId . '|' . $normalizedLocation;
            return isset($this->dealerLocationMapByCompanyAndName[$key])
                ? [(int) $this->dealerLocationMapByCompanyAndName[$key]]
                : [];
        }

        return isset($this->dealerLocationMapByCompany[$normalizedLocation])
            ? [(int) $this->dealerLocationMapByCompany[$normalizedLocation]]
            : [];
    }

    private function resolveRoleId($roleId, ?string $roleName, string $isUser): ?int
    {
        if ($isUser !== 'yes') {
            return null;
        }

        if ($roleId !== null && $roleId !== '') {
            return (int) $roleId;
        }

        $roleName = strtolower(trim((string) $roleName));
        if ($roleName === '') {
            return null;
        }
        return $this->roleMapByName[$roleName] ?? null;
    }

    private function preloadLookupMaps(): void
    {
        $this->designationMap = Designation::query()
            ->pluck('id', 'designation_name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
            ->all();

        $this->companyMap = Company::query()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim((string) $name)) => (int) $id])
            ->all();
        $this->companyNamesById = Company::query()
            ->pluck('name', 'id')
            ->mapWithKeys(fn($name, $id) => [(int) $id => trim((string) $name)])
            ->all();

        $segments = Segment::query()->get(['id', 'name']);
        foreach ($segments as $segment) {
            $this->segmentMap[strtolower(trim((string) $segment->name))] = (int) $segment->id;
            $this->allSegmentIds[] = (int) $segment->id;
        }

        $departments = Department::query()->get(['id', 'department_name']);
        foreach ($departments as $department) {
            $this->departmentMap[strtolower(trim((string) $department->department_name))] = (int) $department->id;
            $this->allDepartmentIds[] = (int) $department->id;
        }

        $subSegments = SubSegment::query()->get(['id', 'segment_id', 'subsegment_name']);
        foreach ($subSegments as $subSegment) {
            $nameKey = strtolower(trim((string) $subSegment->subsegment_name));
            $segmentId = (int) $subSegment->segment_id;
            $subSegmentId = (int) $subSegment->id;

            $this->subSegmentMapByName[$nameKey] = $subSegmentId;
            $this->subSegmentMapBySegmentAndName[$segmentId . '|' . $nameKey] = $subSegmentId;
            $this->allSubSegmentIdsBySegment[$segmentId][] = $subSegmentId;
        }

        $dealerLocations = DealerLocation::query()->get(['id', 'company_id', 'name']);
        foreach ($dealerLocations as $dealerLocation) {
            $locationName = strtolower(trim((string) $dealerLocation->name));
            $locationId = (int) $dealerLocation->id;
            $companyId = (int) $dealerLocation->company_id;

            $this->dealerLocationMapByCompany[$locationName] = $locationId;
            $this->dealerLocationMapByCompanyAndName[$companyId . '|' . $locationName] = $locationId;
            $this->allDealerLocationIdsByCompany[$companyId][] = $locationId;
        }

        $roles = Role::query()->get(['id', 'name']);
        foreach ($roles as $role) {
            $roleId = (int) $role->id;
            $roleName = strtolower(trim((string) $role->name));
            $this->roleMapByName[$roleName] = $roleId;
            $this->roleMapByName['_id_' . $roleId] = (string) $role->name;
        }
    }

    private function warnSkip(string $email, string $reason): void
    {
        $this->skippedCount++;
        $message = "EmployeeJsonSeeder warning [{$email}]: {$reason}";
        $this->command?->warn($message);
        Log::warning($message);
    }
}
