@php
  $nodeId = (int) $employeeId;
  $level = $level ?? 0;
  $visited = $visited ?? [];
  if (isset($visited[$nodeId]) || !isset($employeesById[$nodeId])) {
      return;
  }
  $visited[$nodeId] = true;
  $employee = $employeesById[$nodeId];
  $children = $childrenByManager[$nodeId] ?? [];
  $childrenId = 'children-of-' . $nodeId . '-' . $level;
@endphp

<li class="{{ $level === 0 ? 'root-node' : '' }}">
  <div class="hierarchy-card {{ !empty($children) ? 'manager-node' : 'member-node' }}">
    <div class="hierarchy-node-row">
      @if(!empty($children))
        <button type="button" class="toggle-node-btn" data-target="{{ $childrenId }}" title="Expand / Collapse">
          <i class="bx bx-minus"></i>
        </button>
      @else
        <span style="width:24px;height:24px;display:inline-block;"></span>
      @endif

      <div class="flex-grow-1">
        <div class="fw-semibold">{{ $employee->employee_name }}</div>
        <div class="node-meta text-muted">
          {{ $employee->emp_code }} | {{ $employee->designation->designation_name ?? 'N/A' }}
        </div>
        <div class="node-meta text-muted">{{ $employee->company->name ?? 'No Company' }}</div>
      </div>

      <span class="badge bg-label-primary">Team: {{ count($children) }}</span>
    </div>
  </div>

  @if(!empty($children))
    <ul id="{{ $childrenId }}" class="hierarchy-children">
      @foreach($children as $childId)
        @include('employee.partials.hierarchy-node', [
          'employeeId' => $childId,
          'employeesById' => $employeesById,
          'childrenByManager' => $childrenByManager,
          'visited' => $visited,
          'level' => $level + 1,
        ])
      @endforeach
    </ul>
  @endif
</li>

