@extends('layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Contact List</h2>
        <a href="{{ route('contacts.create') }}" class="btn btn-primary">Add Contact</a>
    </div>

    <form action="{{ route('contacts.importXml') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="file" name="xml_file" accept=".xml" class="form-control" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Import XML</button>
            </div>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td>{{ $contact->name }}</td>
                        <td>{{ $contact->phone }}</td>
                        <td>
                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('contacts.destroy', $contact) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this contact?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No contacts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $contacts->links() }}
        </div>

    </div>
@endsection