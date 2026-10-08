@extends('layouts.admin')

@section('title', 'Attendance Locations')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Attendance Locations</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Manage Locations</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                        <i class="fas fa-plus me-2"></i>Add New Location
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Location Name</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Radius (Meters)</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                <tr>
                                    <td class="ps-4">{{ ($locations->currentPage()-1) * $locations->perPage() + $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $location->name }}</td>
                                    <td>{{ $location->latitude }}</td>
                                    <td>{{ $location->longitude }}</td>
                                    <td>{{ $location->radius }}m</td>
                                    <td>
                                        @if($location->is_active)
                                            <span class="badge bg-success-subtle text-success px-3">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light text-primary me-2" onclick="editLocation({{ $location }})"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No locations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($locations->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $locations->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Location</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.locations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4 position-relative">
                        <label class="form-label fw-bold"><i class="fas fa-search me-2"></i>Search Location (Google Maps)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-primary"></i></span>
                            <input type="text" id="locationSearch" class="form-control border-start-0 ps-0" placeholder="Type to search (e.g. Ranchi College)..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="list-group shadow position-absolute w-100 z-3 d-none" style="max-height: 200px; overflow-y: auto;">
                            <!-- Results will appear here -->
                        </div>
                        <div id="searchLoader" class="spinner-border spinner-border-sm text-primary position-absolute d-none" style="right: 15px; top: 40px;"></div>
                    </div>

                    <hr class="text-muted">

                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" id="locationName" class="form-control" placeholder="e.g. Main Campus Gate" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" id="locationLat" class="form-control" placeholder="e.g. 23.3441" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" id="locationLng" class="form-control" placeholder="e.g. 85.3091" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (in meters)</label>
                        <input type="number" name="radius" class="form-control" value="100" required>
                        <small class="text-muted">Attendance will be allowed within this radius.</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Set as Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Location</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLocationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" id="edit_latitude" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" id="edit_longitude" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (in meters)</label>
                        <input type="number" name="radius" id="edit_radius" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_isActive">
                            <label class="form-check-label" for="edit_isActive">Set as Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editLocation(location) {
    document.getElementById('edit_name').value = location.name;
    document.getElementById('edit_latitude').value = location.latitude;
    document.getElementById('edit_longitude').value = location.longitude;
    document.getElementById('edit_radius').value = location.radius;
    document.getElementById('edit_isActive').checked = location.is_active;
    
    document.getElementById('editLocationForm').action = "{{ url('admin/locations') }}/" + location.id;
    
    var editModal = new bootstrap.Modal(document.getElementById('editLocationModal'));
    editModal.show();
}

// Google Places Search Logic
const locationSearch = document.getElementById('locationSearch');
const searchResults = document.getElementById('searchResults');
const searchLoader = document.getElementById('searchLoader');
let searchTimeout;

locationSearch.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value;
    
    if (query.length < 3) {
        searchResults.classList.add('d-none');
        return;
    }

    searchLoader.classList.remove('d-none');

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.locations.search.autocomplete') }}?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                searchLoader.classList.add('d-none');
                if (data.predictions && data.predictions.length > 0) {
                    let html = '';
                    data.predictions.forEach(prediction => {
                        html += `
                            <button type="button" class="list-group-item list-group-item-action py-2" onclick="selectPlace('${prediction.place_id}', '${prediction.structured_formatting.main_text}')">
                                <i class="fas fa-map-marker-alt text-muted me-2 small"></i>
                                <strong>${prediction.structured_formatting.main_text}</strong>
                                <br><small class="text-muted ms-4">${prediction.structured_formatting.secondary_text}</small>
                            </button>
                        `;
                    });
                    searchResults.innerHTML = html;
                    searchResults.classList.remove('d-none');
                } else {
                    searchResults.classList.add('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                searchLoader.classList.add('d-none');
            });
    }, 500);
});

function selectPlace(placeId, mainText) {
    searchResults.classList.add('d-none');
    locationSearch.value = mainText;
    searchLoader.classList.remove('d-none');

    fetch(`{{ route('admin.locations.search.details') }}?place_id=${placeId}`)
        .then(response => response.json())
        .then(data => {
            searchLoader.classList.add('d-none');
            if (data.result) {
                document.getElementById('locationName').value = data.result.name;
                document.getElementById('locationLat').value = data.result.geometry.location.lat;
                document.getElementById('locationLng').value = data.result.geometry.location.lng;
            }
        })
        .catch(err => {
            console.error(err);
            searchLoader.classList.add('d-none');
        });
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!locationSearch.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('d-none');
    }
});
</script>
@endsection
