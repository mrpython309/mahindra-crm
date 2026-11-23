@extends('layouts/contentNavbarLayout')

@section('title', 'Enquiry Types')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Enquiry Types
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Enquiry Types List</h5>
        <a href="{{ route('enquiry-types.create') }}" class="btn btn-primary">
            Add Enquiry Type
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Enquiry Type Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $enquiryType)
                <tr>
                    <td>{{ $enquiryType->id }}</td>
                    <td>{{ $enquiryType->name }}</td>
                    <td>
                        <a href="{{ route('enquiry-types.edit', $enquiryType->id) }}" class="btn btn-sm btn-warning">
                            <i class="bx bx-edit-alt"></i> Edit
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="deleteEnquiryType({{ $enquiryType->id }})">
                            <i class="bx bx-trash"></i> Delete
                        </button>

                        {{-- Hidden form for delete --}}
                        <form id="delete-form-{{ $enquiryType->id }}" method="POST" action="{{ route('enquiry-types.destroy', $enquiryType->id) }}" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">No Enquiry Types found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function deleteEnquiryType(id) {
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
            // Submit the form
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endsection
