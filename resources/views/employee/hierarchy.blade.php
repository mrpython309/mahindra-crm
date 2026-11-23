@extends('layouts/contentNavbarLayout')

@section('title', 'Employee Hierarchy')

@section('page-style')
<style>
  .hierarchy-tree ul {
    list-style: none;
    margin: 0;
    padding-left: 1.75rem;
    border-left: 1.5px dashed rgba(28, 28, 31, 0.25);
  }
  .hierarchy-tree li {
    margin: 1rem 0;
    position: relative;
  }
  .hierarchy-tree li::before {
    content: '';
    position: absolute;
    top: 2.3rem; /* Perfectly centers the connection on the 74px tall cards */
    left: -1.75rem;
    width: 1.75rem;
    border-top: 1.5px dashed rgba(28, 28, 31, 0.25);
  }
  /* Remove hanging horizontal lines for root-level nodes */
  .hierarchy-tree li.root-node::before {
    display: none !important;
  }
  .hierarchy-root-list {
    border-left: none !important;
    padding-left: 0 !important;
  }
  .hierarchy-card {
    border: 1px solid rgba(28, 28, 31, 0.08) !important;
    background: #ffffff !important;
    border-radius: 10px !important;
    padding: 0.8rem 1.1rem !important;
    min-width: 320px;
    max-width: 560px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.25s ease-in-out;
  }
  /* Left border highlighting manager roles vs members */
  .hierarchy-card.manager-node {
    border-left: 4px solid var(--mahindra-red) !important;
  }
  .hierarchy-card.member-node {
    border-left: 4px solid var(--mahindra-silver-muted) !important;
  }
  .hierarchy-card:hover {
    transform: translateX(4px) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
  }
  .hierarchy-card.manager-node:hover {
    border-left-color: var(--mahindra-red-hover) !important;
  }
  .hierarchy-node-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .toggle-node-btn {
    width: 26px;
    height: 26px;
    border: 1px solid rgba(28, 28, 31, 0.15) !important;
    background: #ffffff !important;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--carbon-text-muted) !important;
  }
  .toggle-node-btn:hover {
    background: var(--mahindra-red) !important;
    color: #ffffff !important;
    border-color: var(--mahindra-red) !important;
  }
  .node-meta {
    font-size: 0.8rem;
  }
  .children-collapsed {
    display: none;
  }
</style>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">Employee Hierarchy</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Hierarchy Tree</h5>
    <a href="{{ route('employee.index') }}" class="btn btn-outline-secondary btn-sm">Back to Employees</a>
  </div>
  <div class="card-body hierarchy-tree">
    <div class="d-flex flex-wrap gap-2 mb-3">
      <span class="badge bg-label-primary">Total Employees: {{ $employeesById->count() }}</span>
      <span class="badge bg-label-info">Top Level: {{ count($rootIds) }}</span>
      <button type="button" id="expandAllNodes" class="btn btn-sm btn-outline-primary">Expand All</button>
      <button type="button" id="collapseAllNodes" class="btn btn-sm btn-outline-secondary">Collapse All</button>
    </div>

    @if(empty($rootIds))
      <p class="text-muted mb-0">No hierarchy data available.</p>
    @else
      <ul class="hierarchy-root-list">
        @foreach($rootIds as $rootId)
          @include('employee.partials.hierarchy-node', [
            'employeeId' => $rootId,
            'employeesById' => $employeesById,
            'childrenByManager' => $childrenByManager,
            'visited' => [],
            'level' => 0,
          ])
        @endforeach
      </ul>
    @endif
  </div>
</div>
@endsection

@section('page-script')
<script>
  (function () {
    function setChildrenVisibility(expand) {
      document.querySelectorAll('.hierarchy-children').forEach(function (el) {
        el.classList.toggle('children-collapsed', !expand);
      });
      document.querySelectorAll('.toggle-node-btn i').forEach(function (icon) {
        icon.className = expand ? 'bx bx-minus' : 'bx bx-plus';
      });
    }

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.toggle-node-btn');
      if (!btn) return;

      const target = document.getElementById(btn.dataset.target);
      if (!target) return;

      const collapsed = target.classList.toggle('children-collapsed');
      const icon = btn.querySelector('i');
      if (icon) icon.className = collapsed ? 'bx bx-plus' : 'bx bx-minus';
    });

    const expandAll = document.getElementById('expandAllNodes');
    const collapseAll = document.getElementById('collapseAllNodes');
    if (expandAll) expandAll.addEventListener('click', function () { setChildrenVisibility(true); });
    if (collapseAll) collapseAll.addEventListener('click', function () { setChildrenVisibility(false); });
  })();
</script>
@endsection

