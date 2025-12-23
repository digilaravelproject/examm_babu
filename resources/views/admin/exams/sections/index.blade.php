@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Sections for: {{ $exam->title }}</h2>

    <button type="button" class="btn btn-primary" onclick="openAddModal()">Add Section</button>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exam->examSections as $es)
            <tr>
                <td>{{ $es->section_order }}</td>
                <td>{{ $es->name }}</td>
                <td>{{ $es->total_duration / 60 }} mins</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="editSection({{ $es->id }})">Edit</button>
                    <form action="{{ route('admin.exams.sections.destroy', [$exam->id, $es->id]) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="sectionForm" method="POST">
            @csrf
            <div id="methodField"></div>
            <div class="modal-content">
                <div class="modal-header"><h5 id="modalTitle">Add Section</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Section Type</label>
                        <select name="section_id" class="form-control" required>
                            @foreach($availableSections as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Display Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = new bootstrap.Modal(document.getElementById('sectionModal'));
    const form = document.getElementById('sectionForm');
    const examId = "{{ $exam->id }}";

    function openAddModal() {
        form.reset();
        document.getElementById('modalTitle').innerText = "Add Section";
        document.getElementById('methodField').innerHTML = "";
        form.action = `/admin/exams/${examId}/sections`;
        modal.show();
    }

    function editSection(id) {
        fetch(`/admin/exams/${examId}/sections/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalTitle').innerText = "Edit Section";
                document.getElementById('methodField').innerHTML = '@method("PUT")';
                form.action = `/admin/exams/${examId}/sections/${id}`;

                // Populate fields
                form.querySelector('[name="name"]').value = data.name;
                form.querySelector('[name="section_id"]').value = data.section_id;
                // Add logic to populate other fields...

                modal.show();
            });
    }
</script>
@endsection
