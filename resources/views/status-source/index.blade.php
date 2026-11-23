@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Response')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Lead Response
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Lead Response List</h5>
        <a href="{{ route('status-sources.create') }}" class="btn btn-primary">
            Add Lead Response
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Response</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row->id }}</td>

                    <td>{{ $row->response }}</td>

                    <td>
                        @if($row->leadStatus->name === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Close</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('status-sources.show', $row->id) }}"
                           class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('status-sources.edit', $row->id) }}"
                           class="btn btn-sm btn-warning">Edit</a>

                        <form id="delete-form-{{ $row->id }}"
                              action="{{ route('status-sources.destroy', $row->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="confirmDelete({{ $row->id }})">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <nav aria-label="Lead Response pagination">
            <ul class="pagination justify-content-center mb-0" id="pagination"></ul>
        </nav>
        <div class="text-center mt-2">
            <small class="text-muted" id="pageInfo">
                Showing {{ $data->count() }} results
            </small>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endsection
