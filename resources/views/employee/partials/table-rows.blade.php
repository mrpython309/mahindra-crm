@forelse($employees as $employee)
    <tr id="row-{{ $employee->id }}">
        <td>{{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}</td>
        <td>{{ $employee->emp_code }}</td>
        <td>{{ $employee->email }}</td>
        <td>{{ $employee->employee_name }}</td>
        <td>{{ $employee->mobile_number }}</td>
        <td>{{ $employee->designation->designation_name ?? '-' }}</td>
        <td>{{ $employee->company->name ?? '-' }}</td>
        <td>{{ $employee->segment_labels ?? '-' }}</td>
        <td>{{ $employee->reports_to_labels ?? '-' }}</td>
        <td>
            <span class="badge bg-{{ $employee->employee_status === 'active' ? 'success' : 'secondary' }}">
                {{ ucfirst($employee->employee_status ?? 'active') }}
            </span>
        </td>
        <td>
            <div class="d-flex gap-1">
                @can('employee:view')
                <a href="{{ route('employee.show', $employee) }}" class="btn btn-sm btn-info" title="View Details">
                    <i class="bx bx-show"></i>
                </a>
                @endcan
                @can('employee:edit')
                <a href="{{ route('employee.edit', $employee) }}" class="btn btn-sm btn-warning" title="Edit">
                    <i class="bx bx-edit"></i>
                </a>
                @endcan
                @can('employee:delete')
                <form action="{{ route('employee.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bx bx-trash"></i>
                    </button>
                </form>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center text-muted">No employees found</td>
    </tr>
@endforelse
