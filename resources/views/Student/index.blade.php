@extends('layouts.main')

@section('content')
    <div class="container">
        <h1 class="text-center">Student List</h1>

        <div>
            <a class="btn btn-primary my-4" id="add-student-btn" href="javascript:void(0)">Add Student</a>
        </div>

        <table class="table my-4" id="student-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>


    {{-- Student Modal --}}
    <div class="modal fade" id="student-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="student-modal-title"></h4>
                </div>
                <div class="modal-body">
                    <form id="student-form">
                        <input type="hidden" name="student_edit_id" id="student_edit_id" value="">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter Name"
                                name="name">
                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="" class="btn btn-primary m-3" id="student-submit-btn">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .error {
            color: red;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"
        integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/additional-methods.min.js"
        integrity="sha512-owaCKNpctt4R4oShUTTraMPFKQWG9UdWTtG6GRzBjFV4VypcFi6+M3yc4Jk85s3ioQmkYWJbUl1b2b2r41RTjA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let studentTable = $('#student-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: "{{ route('student.index') }}",
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });


            $('#student-form').validate({
                rules: {
                    name: {
                        required: true,
                        lettersonly: true,
                        maxlength: 50
                    },
                },
                messages: {
                    name: {
                        required: "Name field is required",
                        maxlength: "Please enter less than 50 character"
                    },
                },

                submitHandler: function(form) {
                    let myform = document.getElementById("student-form");
                    let formData = new FormData(myform);

                    $.ajax({
                        type: "POST",
                        url: '{{ route('student.store') }}',
                        data: formData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                alert(res.message);
                                $('#student-form').trigger("reset");
                                $('#student-modal').modal('hide');
                                studentTable.draw();
                            } else {
                                alert(res.message);
                            }
                        },
                        error: function(response) {
                            if (response.responseJSON.errors) {
                                $.each(response.responseJSON.errors, function(field_name,
                                    error) {
                                    $(document).find('[name=' + field_name + ']')
                                        .after(
                                            '<label class="error" for="' +
                                            field_name +
                                            '">' + error + '</label>')
                                })
                            } else {
                                alert(response.message)
                            }
                        }
                    });
                }
            });

            $(document).on('click', '#add-student-btn', function(e) {
                e.preventDefault();
                $("#student-form").trigger("reset");
                $('label.error').html("");
                $("label.error").hide();
                $("#student_edit_id").val("");
                $("#student-submit-btn").val('Save');
                $("#student-modal-title").html('Create New Student');
                $("#student-modal").modal('show');
            });

            $(document).on('click', '.student-delete-btn', function(e) {
                e.preventDefault();
                let id = $(this).attr('data-id');
                if (confirm('Are you sure want to delete this data')) {
                    $.ajax({
                        type: "DELETE",
                        url: '{{ route('student.delete') }}',
                        data: {
                            'studentId': id,
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                alert(res.message);
                                studentTable.draw();
                            } else {
                                alert(res.message);
                            }
                        },
                    });
                }
            })

            $(document).on('click', '.student-edit-btn', function(e) {
                e.preventDefault();
                $("#student-form").trigger("reset");
                $('label.error').html("");
                $("label.error").hide();
                let id = $(this).attr('data-id');
                $.ajax({
                    type: "POST",
                    url: '{{ route('student.edit') }}',
                    data: {
                        'studentId': id,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $("#student-submit-btn").val('Update');
                            $("#student-modal-title").html('Update User');
                            $("#student_edit_id").val(res.data.student.id);
                            $("#name").val(res.data.student.name);
                            $('#student-modal').modal('show'); 
                        } else {
                            alert(res.message);
                        }
                    },
                });
            })
        });
    </script>
@endPush