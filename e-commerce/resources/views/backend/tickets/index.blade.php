@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Tickets Lists</h6>
      <a href="{{route('product.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add User"><i class="fas fa-plus"></i> Add Product</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($tickets)>0)
        <table class="table table-bordered" id="tickets-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Content</th>
              <th>User</th>
              <th>Status</th>
              <th>Created at</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>

            @foreach($tickets as $ticket)
              @php
              $user = DB::table('users')->where('id',$ticket->user_id)->get()->first();
              $createdDate = date_format(date_create($ticket->created_at),"d/m/Y H:i:s");

              @endphp
                <tr>
                    <td>{{$ticket->id}}</td>
                    <td>{{$ticket->title}}</td>
                    <td>{{$ticket->content}}</td>
                    <td>{{$user->name}}</td>
                    <td>
                        <select name="status" class="ticket-status" data-id="{{ $ticket->id }}" data-route="{{route('editTicket')}}">
                            <option value="new" @if($ticket->status == "new") selected="selected" @endif>new</option>
                            <option value="pending" @if($ticket->status == "pending") selected="selected" @endif>pending</option>
                            <option value="accepted" @if($ticket->status == "accepted") selected="selected" @endif>accepted</option>
                            <option value="refused" @if($ticket->status == "refused") selected="selected" @endif>refused</option>
                        </select>
                    </td>
                    <td>{{$createdDate}}</td>
                    <td>
                    <button data-id="{{ $ticket->id }}" data-route="{{route('deleteTicket')}}" class="btn btn-danger btn-sm dltBtn" data-id="" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </tr>
            @endforeach
          </tbody>
        </table>
        @else
          <h6 class="text-center">No tickets found!!! Please create Product</h6>
        @endif
      </div>
    </div>
</div>
@endsection

  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <style>
      div.dataTables_wrapper div.dataTables_paginate{
          display: none;
      }
      .zoom {
        transition: transform .2s; /* Animation */
      }

      .zoom:hover {
        transform: scale(5);
      }
  </style>


  <!-- Page level plugins -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
        $(document).ready(function(){
            $(".ticket-status").on('change', function (e) {
                $.ajax({
                    url: $(this).attr("data-route"),
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": $(this).attr('data-id'),
                        "status": $(this).val()
                    },
                    success: function (data) {
                        window.location.reload();
                    }
                })
                })

                $(".dltBtn").on('click', function(){
                    $.ajax({
                    url: $(this).attr("data-route"),
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": $(this).attr('data-id'),
                        "status": $(this).val()
                    },
                    success: function (data) {
                        window.location.reload();
                    }
                })
            })
        })
</script>
