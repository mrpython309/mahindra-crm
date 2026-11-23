@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Variant')

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
        .form-section {
            padding: 2rem 2.5rem;
        }


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
            z-index: 1;
        }
        .multi-select-container:focus-within,
        .multi-select-container.active {
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
            z-index: 1000;
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
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #6366f1;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 9999;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            margin: 0;
        }
        .multi-select-dropdown.show {
            display: block !important;
        }
        .multi-select-option {
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .multi-select-option:last-child {
            border-bottom: none;
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
            flex-shrink: 0;
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

        /* Ensure form sections have proper spacing */
        .form-section {
            padding: 2rem 2.5rem;
            position: relative;
        }
        .form-section .row {
            margin-bottom: 1rem;
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
                                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1, #4f46e5); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bx bx-edit text-white" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-semibold">Edit Variant</h5>
                                    <small class="text-muted">Update variant details and configuration</small>
                                </div>
                            </div>
                            <a href="{{ route('variants.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('variants.update', $variant) }}" id="variantForm">
                        @csrf
                        @method('PUT')

                        {{-- Variant Information --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-car text-indigo-600"></i>
                                Variant Information
                            </div>
                            <div class="row g-4">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-medium mb-2">Variant Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control input-modern @error('name') is-invalid @enderror"
                                           name="name"
                                           value="{{ old('name', $variant->name) }}"
                                           required
                                           placeholder="Enter variant name">
                                    @error('name')
                                        <div class="invalid-feedback mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label fw-medium mb-2">Vehicle Model <span class="text-danger">*</span></label>
                                    <select class="form-select input-modern @error('vehicle_model_id') is-invalid @enderror"
                                            name="vehicle_model_id"
                                            id="vehicle_model_select"
                                            required>
                                        <option value="">Select Vehicle Model</option>
                                        @foreach($vehicleModels as $model)
                                            <option value="{{ $model->id }}"
                                                {{ old('vehicle_model_id', $variant->vehicle_model_id) == $model->id ? 'selected' : '' }}>
                                                {{ $model->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_model_id')
                                        <div class="invalid-feedback mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Fuel Type Configuration --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-droplet text-indigo-600"></i>
                                Fuel Type Configuration
                            </div>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-medium mb-2">Available Fuel Types <span class="text-danger">*</span></label>

                                    <div class="multi-select-container" data-target="fuel_type_ids" id="fuel_type_container">
                                        <div class="selected-tags" id="fuel-tags"></div>
                                        <div class="multi-select-placeholder" id="fuel-placeholder">Loading...</div>
                                        <div class="multi-select-dropdown" id="fuel-dropdown">
                                            <!-- Options will be loaded via AJAX based on vehicle model -->
                                        </div>
                                    </div>
                                    <select name="fuel_type_ids[]" multiple style="display: none;" id="fuel_type_ids" required>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                    <small class="text-muted mt-2">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Fuel types are filtered based on the selected vehicle model
                                    </small>
                                    @error('fuel_type_ids')
                                        <div class="invalid-feedback mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Transmission Configuration --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-cog text-indigo-600"></i>
                                Transmission Configuration
                            </div>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-medium mb-2">Available Transmission <span class="text-danger">*</span></label>

                                    <div class="multi-select-container" data-target="transmission_ids" id="transmission_container">
                                        <div class="selected-tags" id="transmission-tags"></div>
                                        <div class="multi-select-placeholder" id="transmission-placeholder">Loading...</div>
                                        <div class="multi-select-dropdown" id="transmission-dropdown">
                                            <!-- Options will be loaded via AJAX based on vehicle model -->
                                        </div>
                                    </div>
                                    <select name="transmission_ids[]" multiple style="display: none;" id="transmission_ids" required>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                    <small class="text-muted mt-2">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Transmissions are filtered based on the selected vehicle model
                                    </small>
                                    @error('transmission_ids')
                                        <div class="invalid-feedback mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Colour Configuration --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-palette text-indigo-600"></i>
                                Colour Configuration
                            </div>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-medium mb-2">Available Colours</label>

                                    <div class="multi-select-container" data-target="colour_ids" id="colour_container">
                                        <div class="selected-tags" id="colour-tags"></div>
                                        <div class="multi-select-placeholder" id="colour-placeholder">Select vehicle model first</div>
                                        <div class="multi-select-dropdown" id="colour-dropdown">
                                            <!-- Options will be loaded via AJAX based on vehicle model -->
                                        </div>
                                    </div>
                                    <select name="colour_ids[]" multiple style="display: none;" id="colour_ids">
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                    <small class="text-muted mt-2">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Colours will be loaded based on the selected vehicle model.
                                    </small>
                                    @error('colour_ids')
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
                                            <input class="form-check-input" type="checkbox" id="statusSwitch"
                                                {{ old('status', $variant->status) === 'active' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="statusSwitch">
                                                Variant is ready for use
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="active" {{ old('status', $variant->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $variant->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="p-4 border-top bg-gray-50">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('variants.index') }}" class="btn btn-outline-secondary px-5 py-2 fs-6">
                                    <i class="bx bx-x me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-indigo-modern px-6 py-2 fs-6">
                                    <i class="bx bx-check me-2"></i>Update Variant
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
// Wait for jQuery to be available (Vite loads asynchronously)
(function checkJQuery() {
    if (typeof jQuery !== 'undefined') {
        // jQuery is ready, initialize page
        initializeVariantForm();
    } else {
        // jQuery not ready yet, wait
        setTimeout(checkJQuery, 50);
    }
})();

function initializeVariantForm() {
$(function() {
    console.log('=== Initializing Variant Edit Form ===');

    // Multi-select functionality
    function initMultiSelect(container, dropdown, tagsContainer, placeholder, hiddenSelect) {
        // Toggle dropdown
        container.on('click', function(e) {
            e.stopPropagation();

            // Close other dropdowns first
            $('.multi-select-dropdown').not(dropdown).removeClass('show').hide();
            $('.multi-select-container').not(container).removeClass('active');

            // Toggle current dropdown
            if (dropdown.hasClass('show')) {
                dropdown.removeClass('show').hide();
                container.removeClass('active');
            } else {
                dropdown.addClass('show').show();
                container.addClass('active');
            }
        });

        // Close dropdowns on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.multi-select-container').length) {
                $('.multi-select-dropdown').removeClass('show').hide();
                $('.multi-select-container').removeClass('active');
            }
        });
    }

    function addTag(value, text, tagsContainer, hiddenSelect) {
        // Check if tag already exists
        if (tagsContainer.find(`[data-value="${value}"]`).length > 0) {
            return;
        }

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
            const container = tagsContainer.closest('.multi-select-container');
            const option = container.find(`.multi-select-option[data-value="${value}"]`);
            option.removeClass('selected');
            option.find('.option-checkbox').removeClass('checked').html('');
            updatePlaceholder(tagsContainer, tagsContainer.siblings('.multi-select-placeholder'));
        });

        tagsContainer.append(tag);
        hiddenSelect.find(`option[value="${value}"]`).prop('selected', true);
        console.log('Added tag:', value, text);
    }

    function removeTag(value, tagsContainer, hiddenSelect) {
        tagsContainer.find(`[data-value="${value}"]`).remove();
        hiddenSelect.find(`option[value="${value}"]`).prop('selected', false);
        console.log('Removed tag:', value);
    }

    function updatePlaceholder(tagsContainer, placeholder) {
        if (tagsContainer.children().length > 0) {
            placeholder.hide();
        } else {
            placeholder.show();
        }
    }

    // Load fuel types function
    function loadFuelTypes(vehicleModelId) {
        console.log('Loading fuel types for model:', vehicleModelId);

        const fuelContainer = $('#fuel_type_container');
        const fuelDropdown = $('#fuel-dropdown');
        const fuelTags = $('#fuel-tags');
        const fuelPlaceholder = $('#fuel-placeholder');
        const fuelHiddenSelect = $('#fuel_type_ids');

        if (!vehicleModelId) {
            // Reset
            fuelDropdown.empty();
            fuelTags.empty();
            fuelPlaceholder.text('Select vehicle model first').show();
            fuelHiddenSelect.empty();
            fuelContainer.removeClass('active');
            return;
        }

        // Show loading
        fuelPlaceholder.text('Loading...').show();
        fuelTags.empty();
        fuelHiddenSelect.empty();

        // Build URL
        const url = '{{ route("variants.get-fuel-types") }}?vehicle_model_id=' + vehicleModelId;

        // Get selected fuel type IDs from backend
        @if(old('fuel_type_ids'))
            var selectedFuelTypeIds = {!! json_encode(old('fuel_type_ids')) !!}.map(String);
        @else
            var selectedFuelTypeIds = {!! json_encode($variant->fuelTypes->pluck('id')->toArray()) !!};
        @endif

        // Make AJAX request
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                console.log('Fuel types response:', data);

                fuelDropdown.empty();
                fuelHiddenSelect.empty();

                if (data.fuel_types && data.fuel_types.length > 0) {
                    console.log('Found ' + data.fuel_types.length + ' fuel types');

                    data.fuel_types.forEach(function(fuelType) {
                        const isSelected = selectedFuelTypeIds.includes(fuelType.id) || selectedFuelTypeIds.includes(String(fuelType.id));

                        // Add to dropdown
                        const optionHtml = `
                            <div class="multi-select-option ${isSelected ? 'selected' : ''}" data-value="${fuelType.id}">
                                <div class="option-checkbox ${isSelected ? 'checked' : ''}">${isSelected ? '<i class="bx bx-check" style="font-size: 12px; color: white;"></i>' : ''}</div>
                                <span>${fuelType.name}</span>
                            </div>
                        `;
                        fuelDropdown.append(optionHtml);

                        // Add to hidden select
                        fuelHiddenSelect.append(`<option value="${fuelType.id}" ${isSelected ? 'selected' : ''}>${fuelType.name}</option>`);

                        // Add tag if selected
                        if (isSelected) {
                            addTag(fuelType.id, fuelType.name, fuelTags, fuelHiddenSelect);
                        }
                    });

                    // Bind click events to options
                    fuelDropdown.find('.multi-select-option').on('click', function(e) {
                        e.stopPropagation();
                        const option = $(this);
                        const value = option.data('value');
                        const text = option.find('span').text();
                        const checkbox = option.find('.option-checkbox');

                        if (option.hasClass('selected')) {
                            // Deselect
                            option.removeClass('selected');
                            checkbox.removeClass('checked').html('');
                            removeTag(value, fuelTags, fuelHiddenSelect);
                        } else {
                            // Select
                            option.addClass('selected');
                            checkbox.addClass('checked').html('<i class="bx bx-check" style="font-size: 12px; color: white;"></i>');
                            addTag(value, text, fuelTags, fuelHiddenSelect);
                        }

                        updatePlaceholder(fuelTags, fuelPlaceholder);
                    });

                    // Initialize multi-select for fuel types
                    initMultiSelect(fuelContainer, fuelDropdown, fuelTags, fuelPlaceholder, fuelHiddenSelect);

                    updatePlaceholder(fuelTags, fuelPlaceholder);
                    if (fuelTags.children().length === 0) {
                        fuelPlaceholder.text('Select fuel types').show();
                    }
                    console.log('Fuel types loaded successfully!');
                } else {
                    fuelPlaceholder.text('No fuel types available').show();
                }
            },
            error: function(error) {
                console.error('Error loading fuel types:', error);
                fuelPlaceholder.text('Error loading fuel types').show();
            }
        });
    }

    // Load transmissions function
    function loadTransmissions(vehicleModelId) {
        console.log('Loading transmissions for model:', vehicleModelId);

        const transmissionContainer = $('#transmission_container');
        const transmissionDropdown = $('#transmission-dropdown');
        const transmissionTags = $('#transmission-tags');
        const transmissionPlaceholder = $('#transmission-placeholder');
        const transmissionHiddenSelect = $('#transmission_ids');

        if (!vehicleModelId) {
            // Reset
            transmissionDropdown.empty();
            transmissionTags.empty();
            transmissionPlaceholder.text('Select vehicle model first').show();
            transmissionHiddenSelect.empty();
            transmissionContainer.removeClass('active');
            return;
        }

        // Show loading
        transmissionPlaceholder.text('Loading...').show();
        transmissionTags.empty();
        transmissionHiddenSelect.empty();

        // Build URL
        const url = '{{ route("variants.get-transmissions") }}?vehicle_model_id=' + vehicleModelId;

        // Get selected transmission IDs from backend
        @if(old('transmission_ids'))
            var selectedTransmissionIds = {!! json_encode(old('transmission_ids')) !!}.map(String);
        @else
            var selectedTransmissionIds = {!! json_encode($variant->transmissions->pluck('id')->toArray()) !!};
        @endif

        // Make AJAX request
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                console.log('Transmissions response:', data);

                transmissionDropdown.empty();
                transmissionHiddenSelect.empty();

                if (data.transmissions && data.transmissions.length > 0) {
                    console.log('Found ' + data.transmissions.length + ' transmissions');

                    data.transmissions.forEach(function(transmission) {
                        const isSelected = selectedTransmissionIds.includes(transmission.id) || selectedTransmissionIds.includes(String(transmission.id));

                        // Add to dropdown
                        const optionHtml = `
                            <div class="multi-select-option ${isSelected ? 'selected' : ''}" data-value="${transmission.id}">
                                <div class="option-checkbox ${isSelected ? 'checked' : ''}">${isSelected ? '<i class="bx bx-check" style="font-size: 12px; color: white;"></i>' : ''}</div>
                                <span>${transmission.name}</span>
                            </div>
                        `;
                        transmissionDropdown.append(optionHtml);

                        // Add to hidden select
                        transmissionHiddenSelect.append(`<option value="${transmission.id}" ${isSelected ? 'selected' : ''}>${transmission.name}</option>`);

                        // Add tag if selected
                        if (isSelected) {
                            addTag(transmission.id, transmission.name, transmissionTags, transmissionHiddenSelect);
                        }
                    });

                    // Bind click events to options
                    transmissionDropdown.find('.multi-select-option').on('click', function(e) {
                        e.stopPropagation();
                        const option = $(this);
                        const value = option.data('value');
                        const text = option.find('span').text();
                        const checkbox = option.find('.option-checkbox');

                        if (option.hasClass('selected')) {
                            // Deselect
                            option.removeClass('selected');
                            checkbox.removeClass('checked').html('');
                            removeTag(value, transmissionTags, transmissionHiddenSelect);
                        } else {
                            // Select
                            option.addClass('selected');
                            checkbox.addClass('checked').html('<i class="bx bx-check" style="font-size: 12px; color: white;"></i>');
                            addTag(value, text, transmissionTags, transmissionHiddenSelect);
                        }

                        updatePlaceholder(transmissionTags, transmissionPlaceholder);
                    });

                    // Initialize multi-select for transmissions
                    initMultiSelect(transmissionContainer, transmissionDropdown, transmissionTags, transmissionPlaceholder, transmissionHiddenSelect);

                    updatePlaceholder(transmissionTags, transmissionPlaceholder);
                    if (transmissionTags.children().length === 0) {
                        transmissionPlaceholder.text('Select transmissions').show();
                    }
                    console.log('Transmissions loaded successfully!');
                } else {
                    transmissionPlaceholder.text('No transmissions available').show();
                }
            },
            error: function(error) {
                console.error('Error loading transmissions:', error);
                transmissionPlaceholder.text('Error loading transmissions').show();
            }
        });
    }

    // Load colours function - loads colours based on selected vehicle model
    function loadColours(vehicleModelId) {
        console.log('Loading colours for model:', vehicleModelId);

        const colourContainer = $('#colour_container');
        const colourDropdown = $('#colour-dropdown');
        const colourTags = $('#colour-tags');
        const colourPlaceholder = $('#colour-placeholder');
        const colourHiddenSelect = $('#colour_ids');

        if (!vehicleModelId) {
            // Reset
            colourDropdown.empty();
            colourTags.empty();
            colourPlaceholder.text('Select vehicle model first').show();
            colourHiddenSelect.empty();
            colourContainer.removeClass('active');
            return;
        }

        // Show loading
        colourPlaceholder.text('Loading...').show();
        colourTags.empty();
        colourHiddenSelect.empty();

        // Build URL
        const url = '{{ route("variants.get-colours") }}?vehicle_model_id=' + vehicleModelId;

        // Get selected colour IDs from backend
        @if(old('colour_ids'))
            var selectedColourIds = {!! json_encode(old('colour_ids')) !!}.map(String);
        @else
            var selectedColourIds = {!! json_encode($variant->colours->pluck('id')->toArray()) !!};
        @endif

        // Make AJAX request
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                console.log('Colours response:', data);

                colourDropdown.empty();
                colourHiddenSelect.empty();

                if (data.colours && data.colours.length > 0) {
                    console.log('Found ' + data.colours.length + ' colours');

                    data.colours.forEach(function(colour) {
                        const isSelected = selectedColourIds.includes(colour.id) || selectedColourIds.includes(String(colour.id));

                        // Add to dropdown
                        const optionHtml = `
                            <div class="multi-select-option ${isSelected ? 'selected' : ''}" data-value="${colour.id}">
                                <div class="option-checkbox ${isSelected ? 'checked' : ''}">${isSelected ? '<i class="bx bx-check" style="font-size: 12px; color: white;"></i>' : ''}</div>
                                <span>${colour.name}</span>
                            </div>
                        `;
                        colourDropdown.append(optionHtml);

                        // Add to hidden select
                        colourHiddenSelect.append(`<option value="${colour.id}" ${isSelected ? 'selected' : ''}>${colour.name}</option>`);

                        // Add tag if selected
                        if (isSelected) {
                            addTag(colour.id, colour.name, colourTags, colourHiddenSelect);
                        }
                    });

                    // Bind click events to options
                    colourDropdown.find('.multi-select-option').on('click', function(e) {
                        e.stopPropagation();
                        const option = $(this);
                        const value = option.data('value');
                        const text = option.find('span').text();
                        const checkbox = option.find('.option-checkbox');

                        if (option.hasClass('selected')) {
                            // Deselect
                            option.removeClass('selected');
                            checkbox.removeClass('checked').html('');
                            removeTag(value, colourTags, colourHiddenSelect);
                        } else {
                            // Select
                            option.addClass('selected');
                            checkbox.addClass('checked').html('<i class="bx bx-check" style="font-size: 12px; color: white;"></i>');
                            addTag(value, text, colourTags, colourHiddenSelect);
                        }

                        updatePlaceholder(colourTags, colourPlaceholder);
                    });

                    // Initialize multi-select for colours
                    initMultiSelect(colourContainer, colourDropdown, colourTags, colourPlaceholder, colourHiddenSelect);

                    updatePlaceholder(colourTags, colourPlaceholder);
                    if (colourTags.children().length === 0) {
                        colourPlaceholder.text('Select colours').show();
                    }
                    console.log('Colours loaded successfully!');
                } else {
                    colourPlaceholder.text('No colours available').show();
                }
            },
            error: function(error) {
                console.error('Error loading colours:', error);
                colourPlaceholder.text('Error loading colours').show();
            }
        });
    }

    // Vehicle model change event
    $('#vehicle_model_select').on('change', function() {
        const vehicleModelId = $(this).val();
        console.log('Vehicle model changed to:', vehicleModelId);
        loadFuelTypes(vehicleModelId);
        loadTransmissions(vehicleModelId);
        loadColours(vehicleModelId);
    });

    // Status toggle sync
    $('#statusSwitch').on('change', function() {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });

    // Form validation
    if (typeof FormValidation !== 'undefined') {
        FormValidation.formValidation(document.getElementById('variantForm'), {
            fields: {
                name: { validators: { notEmpty: { message: 'Variant name is required' } } },
                vehicle_model_id: { validators: { notEmpty: { message: 'Please select a vehicle model' } } },
                'fuel_type_ids[]': { validators: { choice: { min: 1, message: 'Select at least one fuel type' } } },
                'transmission_ids[]': { validators: { choice: { min: 1, message: 'Select at least one transmission' } } }
            },
            plugins: {
                bootstrap5: new FormValidation.plugins.Bootstrap5(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        });
    }

    // Load fuel types, transmissions, and colours for current vehicle model on page load
    const initialVehicleModelId = $('#vehicle_model_select').val();
    if (initialVehicleModelId) {
        loadFuelTypes(initialVehicleModelId);
        loadTransmissions(initialVehicleModelId);
        loadColours(initialVehicleModelId);
    }

    console.log('=== Variant Edit Form Initialization Complete ===');
});
} // End initializeVariantForm
</script>
@endsection
