@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Vehicle Model')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('page-style')
<style>
    .form-modern {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        max-width: 100%;
        margin: 0;
    }
    .input-modern {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.875rem 1.25rem;
        transition: all 0.2s ease;
        background: #fafbfc;
        height: 56px;
    }
    .input-modern:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        transform: translateY(-1px);
    }
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .btn-indigo-modern {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        color: white;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .status-card {
        background: linear-gradient(135deg, #f7f5ff 0%, #f0f2ff 100%);
        border: 1px solid #e0e7ff;
        border-radius: 12px;
        padding: 1.75rem;
        margin: 1rem 0;
    }
    .form-section { padding: 2rem 2.5rem; }

    /* Modern Multi-Select Styling */
    .multi-select-container {
        position: relative;
        min-height: 56px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: #fafbfc;
        transition: all 0.2s ease;
        padding: 8px;
        cursor: pointer;
    }
    .multi-select-container:focus-within {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        transform: translateY(-1px);
    }
    .selected-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 6px;
        min-height: 32px;
        align-items: center;
    }
    .tag-item {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 6px;
        animation: tagAppear 0.2s ease;
    }
    .tag-remove {
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }
    .tag-remove:hover {
        background: rgba(255,255,255,0.3);
    }
    .multi-select-placeholder {
        color: #9ca3af;
        font-size: 0.875rem;
        padding: 8px;
    }
    .multi-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }
    .multi-select-option {
        padding: 12px 16px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .multi-select-option:hover {
        background: #f3f4f6;
    }
    .multi-select-option.selected {
        background: #eff6ff;
        color: #2563eb;
        font-weight: 500;
    }
    .option-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .option-checkbox.checked {
        background: #6366f1;
        border-color: #6366f1;
        color: white;
    }
    @keyframes tagAppear {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="row g-0">
        <div class="col-12">

            <div class="form-modern mb-4">

                {{-- Header --}}
                <div class="p-4 border-bottom" style="background: linear-gradient(135deg, #f7f5ff 0%, #f0f2ff 100%);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:56px;height:56px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:16px;display:flex;align-items:center;justify-content:center;">
                                <i class="bx bx-car text-white" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-semibold">Edit Vehicle Model</h5>
                                <small class="text-muted">Update vehicle model details</small>
                            </div>
                        </div>
                        <a href="{{ route('vehicle-models.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                {{-- FORM --}}
                <form method="POST"
                      action="{{ route('vehicle-models.update', $vehicleModel) }}"
                      id="modelForm">
                    @csrf
                    @method('PUT')

                    {{-- Model Name --}}
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-car text-indigo-600"></i>
                            Model Information
                        </div>
                        <div class="row g-4">
                            <div class="col-12 col-lg-8">
                                <label class="form-label fw-medium mb-2">
                                    Model Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control input-modern @error('name') is-invalid @enderror"
                                       name="name"
                                       value="{{ old('name', $vehicleModel->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Configuration --}}
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-grid-alt text-indigo-600"></i>
                            Configuration
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-medium mb-2">Segment *</label>
                                <select class="form-select input-modern" name="segment_id" required>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}"
                                            {{ old('segment_id', $vehicleModel->segment_id) == $segment->id ? 'selected' : '' }}>
                                            {{ $segment->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-medium mb-2">Fuel Type *</label>
                                <div class="multi-select-container" data-target="fuel_type_ids">
                                    <div class="selected-tags" id="fuel-tags"></div>
                                    <div class="multi-select-placeholder" id="fuel-placeholder">Select fuel types</div>
                                    <div class="multi-select-dropdown" id="fuel-dropdown">
                                        @foreach($fuelTypes as $fuelType)
                                            <div class="multi-select-option" data-value="{{ $fuelType->id }}">
                                                <div class="option-checkbox"></div>
                                                <span>{{ $fuelType->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <select name="fuel_type_ids[]" multiple style="display: none;" id="fuel_type_ids" required>
                                    @foreach($fuelTypes as $fuelType)
                                        <option value="{{ $fuelType->id }}"
                                            {{ in_array($fuelType->id, old('fuel_type_ids', $vehicleModel->fuelTypes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $fuelType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fuel_type_ids')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-medium mb-2">Transmission *</label>
                                <div class="multi-select-container" data-target="transmission_ids">
                                    <div class="selected-tags" id="transmission-tags"></div>
                                    <div class="multi-select-placeholder" id="transmission-placeholder">Select transmissions</div>
                                    <div class="multi-select-dropdown" id="transmission-dropdown">
                                        @foreach($transmissions as $transmission)
                                            <div class="multi-select-option" data-value="{{ $transmission->id }}">
                                                <div class="option-checkbox"></div>
                                                <span>{{ $transmission->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <select name="transmission_ids[]" multiple style="display: none;" id="transmission_ids" required>
                                    @foreach($transmissions as $transmission)
                                        <option value="{{ $transmission->id }}"
                                            {{ in_array($transmission->id, old('transmission_ids', $vehicleModel->transmissions->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $transmission->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('transmission_ids')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-medium mb-2">Company *</label>
                                <div class="multi-select-container" data-target="company_ids">
                                    <div class="selected-tags" id="company-tags"></div>
                                    <div class="multi-select-placeholder" id="company-placeholder">Select companies</div>
                                    <div class="multi-select-dropdown" id="company-dropdown">
                                        @foreach($companies as $company)
                                            <div class="multi-select-option" data-value="{{ $company->id }}">
                                                <div class="option-checkbox"></div>
                                                <span>{{ $company->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <select name="company_ids[]" multiple style="display: none;" id="company_ids" required>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ in_array($company->id, old('company_ids', $vehicleModel->companies->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_ids')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-medium mb-2">Colour *</label>
                                <div class="multi-select-container" data-target="colour_ids">
                                    <div class="selected-tags" id="colour-tags"></div>
                                    <div class="multi-select-placeholder" id="colour-placeholder">Select colours</div>
                                    <div class="multi-select-dropdown" id="colour-dropdown">
                                        @foreach($colours as $colour)
                                            <div class="multi-select-option" data-value="{{ $colour->id }}">
                                                <div class="option-checkbox"></div>
                                                <span>{{ $colour->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <select name="colour_ids[]" multiple style="display: none;" id="colour_ids" required>
                                    @foreach($colours as $colour)
                                        <option value="{{ $colour->id }}"
                                            {{ in_array($colour->id, old('colour_ids', $vehicleModel->colours->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $colour->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('colour_ids')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-medium mb-2">Dealer Location *</label>
                                <div class="multi-select-container" data-target="dealer_location_ids">
                                    <div class="selected-tags" id="dealer-tags"></div>
                                    <div class="multi-select-placeholder" id="dealer-placeholder">Select dealer locations</div>
                                    <div class="multi-select-dropdown" id="dealer-dropdown">
                                        @foreach($dealerLocations as $location)
                                            <div class="multi-select-option" data-value="{{ $location->id }}">
                                                <div class="option-checkbox"></div>
                                                <span>{{ $location->name }} ({{ $location->city }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <select name="dealer_location_ids[]" multiple style="display: none;" id="dealer_location_ids" required>
                                    @foreach($dealerLocations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ in_array($location->id, old('dealer_location_ids', $vehicleModel->dealerLocations->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $location->name }} ({{ $location->city }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('dealer_location_ids')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-toggle-right text-indigo-600"></i>
                            Status
                        </div>

                        <div class="status-card mx-4">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-8">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="statusSwitch"
                                               {{ old('status', $vehicleModel->status) === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">
                                            Model is ready for use
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" {{ old('status', $vehicleModel->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $vehicleModel->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="p-4 border-top bg-gray-50">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('vehicle-models.index') }}" class="btn btn-outline-secondary px-5 py-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-indigo-modern px-6 py-2">
                                <i class="bx bx-check me-2"></i>Update Model
                            </button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/bootstrap5.min.js') }}"></script>
@endsection

@section('page-script')
<script>
// Wait for jQuery to be available (it may load asynchronously)
(function checkJQuery() {
    if (typeof jQuery !== 'undefined') {
        initializeVehicleModelEditPage();
    } else {
        setTimeout(checkJQuery, 50);
    }
})();

function initializeVehicleModelEditPage() {
    $(document).ready(function () {
        // Multi-select functionality
        function initMultiSelect() {
            $('.multi-select-container').each(function() {
                const container = $(this);
                const tagsContainer = container.find('.selected-tags');
                const placeholder = container.find('.multi-select-placeholder');
                const dropdown = container.find('.multi-select-dropdown');
                const hiddenSelect = $('#' + container.data('target'));

                // Toggle dropdown
                container.on('click', function(e) {
                    e.stopPropagation();
                    $('.multi-select-dropdown').not(dropdown).hide();
                    dropdown.toggle();
                });

                // Option selection
                container.find('.multi-select-option').on('click', function(e) {
                    e.stopPropagation();
                    const option = $(this);
                    const value = option.data('value');
                    const text = option.find('span').text();
                    const checkbox = option.find('.option-checkbox');

                    if (option.hasClass('selected')) {
                        // Deselect
                        option.removeClass('selected');
                        checkbox.removeClass('checked').html('');
                        removeTag(value, tagsContainer, hiddenSelect);
                    } else {
                        // Select
                        option.addClass('selected');
                        checkbox.addClass('checked').html('<i class="bx bx-check" style="font-size: 12px;"></i>');
                        addTag(value, text, tagsContainer, hiddenSelect);
                    }

                    updatePlaceholder(tagsContainer, placeholder);
                });

                // Initialize with existing values
                hiddenSelect.find('option:selected').each(function() {
                    const value = $(this).val();
                    const text = $(this).text();
                    addTag(value, text, tagsContainer, hiddenSelect);
                    container.find(`.multi-select-option[data-value="${value}"]`)
                        .addClass('selected')
                        .find('.option-checkbox')
                        .addClass('checked')
                        .html('<i class="bx bx-check" style="font-size: 12px;"></i>');
                });

                updatePlaceholder(tagsContainer, placeholder);
            });

            // Close dropdowns on outside click
            $(document).on('click', function() {
                $('.multi-select-dropdown').hide();
            });
        }

        function addTag(value, text, tagsContainer, hiddenSelect) {
            const tag = $(`
                <div class="tag-item" data-value="${value}">
                    <span>${text}</span>
                    <div class="tag-remove">
                        <i class="bx bx-x" style="font-size: 12px;"></i>
                    </div>
                </div>
            `);

            tag.find('.tag-remove').on('click', function(e) {
                e.stopPropagation();
                removeTag(value, tagsContainer, hiddenSelect);
                tagsContainer.closest('.multi-select-container')
                    .find(`.multi-select-option[data-value="${value}"]`)
                    .removeClass('selected')
                    .find('.option-checkbox')
                    .removeClass('checked')
                    .html('');
                updatePlaceholder(tagsContainer, tagsContainer.siblings('.multi-select-placeholder'));
            });

            tagsContainer.append(tag);
            hiddenSelect.find(`option[value="${value}"]`).prop('selected', true);
        }

        function removeTag(value, tagsContainer, hiddenSelect) {
            tagsContainer.find(`[data-value="${value}"]`).remove();
            hiddenSelect.find(`option[value="${value}"]`).prop('selected', false);
        }

        function updatePlaceholder(tagsContainer, placeholder) {
            if (tagsContainer.children().length > 0) {
                placeholder.hide();
            } else {
                placeholder.show();
            }
        }

        // Initialize multi-select
        initMultiSelect();

        // Status toggle sync
        $('#statusSwitch').on('change', function () {
            $('#status').val(this.checked ? 'active' : 'inactive');
        });

        // Form validation
        if (typeof FormValidation !== 'undefined') {
            FormValidation.formValidation('#modelForm', {
                fields: {
                    name: { validators: { notEmpty: { message: 'Model name required' } } },
                    segment_id: { validators: { notEmpty: { message: 'Select segment' } } },
                    'transmission_ids[]': { validators: { choice: { min: 1, message: 'Select at least one transmission' } } },
                    'fuel_type_ids[]': { validators: { choice: { min: 1, message: 'Select at least one fuel type' } } },
                    'colour_ids[]': { validators: { choice: { min: 1, message: 'Select at least one colour' } } },
                    'dealer_location_ids[]': { validators: { choice: { min: 1, message: 'Select at least one dealer location' } } },
                    'company_ids[]': { validators: { choice: { min: 1, message: 'Select at least one company' } } }
                },
                plugins: {
                    bootstrap5: new FormValidation.plugins.Bootstrap5(),
                    submitButton: new FormValidation.plugins.SubmitButton()
                }
            });
        }
    });
}
</script>
@endsection
