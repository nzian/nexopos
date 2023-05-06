@extends('layouts.app')

@section('content')
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="form-group">
          <label for="date-filter">Date:</label>
          <input type="text" id="date-filter" class="form-control" value="{{ $selectedDate->format('Y-m-d') }}" readonly>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Room</th>
            @for ($i = 0; $i < 7; $i++)
              <th>{{ $dates[$i]->format('D, M j') }}</th>
            @endfor
          </tr>
        </thead>
        <tbody>
          @foreach ($rooms as $room)
            <tr>
              <td>{{ $room->name }}</td>
              @for ($i = 0; $i < 7; $i++)
                @php
                  $date = $dates[$i];
                  $booking = $bookings[$room->id][$date->format('Y-m-d')] ?? null;
                  $status = $booking ? $booking->status : 'available';
                  $backgroundColor = $status === 'booked' ? '#dc3545' : ($status === 'reserved' ? '#ffc107' : '#28a745');
                  $text = $status === 'booked' ? 'B' : ($status === 'reserved' ? 'R' : '');
                @endphp
                <td class="text-center {{ $status === 'booked' ? 'text-white' : '' }}" style="background-color: {{ $backgroundColor }};">
                  <a href="#booking-modal" data-toggle="modal" data-room-id="{{ $room->id }}" data-date="{{ $date->format('Y-m-d') }}" data-status="{{ $status }}" data-booking-id="{{ $booking ? $booking->id : '' }}" data-text="{{ $text }}">{{ $text }}</a>
                </td>
              @endfor
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="row mt-3">
      <div class="col-md-12">
        <div id="booking-calendar"></div>
      </div>
    </div>
  </div>

  {{-- Booking Modal --}}
  <div class="modal fade" id="booking-modal" tabindex="-1" role="dialog" aria-labelledby="booking-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="booking-form" method="POST">
          @csrf
          <input type="hidden" name="_method" value="POST">
          <input type="hidden" name="room_id" value="">
          <input type="hidden" name="start_date" value="">
          <input type="hidden" name="end_date" value="">
          <div class="modal-header">
            <h5 class="modal-title" id="booking-modal-label">Book Room</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <divclass="form-group">
<label for="booking-status">Status:</label>
<input type="text" id="booking-status" class="form-control" value="" readonly>
</div>
</div>
</div>
<div class="row">
<div class="col-md-6">
<div class="form-group">
<label for="booking-start-date">Start Date:</label>
<input type="text" id="booking-start-date" class="form-control" value="" readonly>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="booking-end-date">End Date:</label>
<input type="text" id="booking-end-date" class="form-control" value="" readonly>
</div>
</div>
</div>
<div class="row">
<div class="col-md-12">
<div class="form-group">
<label for="booking-notes">Notes:</label>
<textarea id="booking-notes" name="notes" class="form-control" rows="5"></textarea>
</div>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
<button type="submit" class="btn btn-primary">Save</button>
</div>
</form>
</div>
</div>

  </div>
@endsection
@section('scripts')

  <script src="{{ asset('js/moment.min.js') }}"></script>
  <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('booking-calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: "{{ route('bookings.events') }}",
        eventClick: function(info) {
          $('#booking-modal input[name="room_id"]').val(info.event.extendedProps.room_id);
          $('#booking-modal input[name="start_date"]').val(info.event.startStr);
          $('#booking-modal input[name="end_date"]').val(info.event.endStr);
          $('#booking-modal input[name="_method"]').val('PUT');
          $('#booking-modal form').attr('action', "{{ route('bookings.update', '') }}/" + info.event.id);
          $('#booking-modal-label').text('Edit Booking');
          $('#booking-status').val(info.event.extendedProps.status);
          $('#booking-start-date').val(moment(info.event.start).format('lll'));
          $('#booking-end-date').val(moment(info.event.end).format('lll'));
          $('#booking-notes').val(info.event.extendedProps.notes);
          $('#booking-modal').modal('show');
        }
      });
      calendar.render();
    });

    $(function() {
      $('#booking-modal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var roomId = button.data('room-id');
        var date = button.data('date');
        var status = button.data('status');
        var bookingId = button.data('booking-id');
        var text = button.data('text');
        var modal = $(this);
        modal.find('.modal-title').text(text === '' ? 'Book Room' : 'Edit Booking');
        modal.find('.modal-body input[name="room_id"]').val(roomId);
        modal.find('.modal-body input[name="start_date"]').val(moment(date).format('YYYY-MM-DD'));
modal.find('.modal-body input[name="end_date"]').val(moment(date).add(1, 'day').format('YYYY-MM-DD'));
modal.find('.modal-body input[name="status"]').val(status);
modal.find('.modal-body input[name="_method"]').val(bookingId === '' ? 'POST' : 'PUT');
modal.find('.modal-footer button[type="submit"]').text(bookingId === '' ? 'Book Room' : 'Save');
modal.find('.modal-body #booking-status').val(status);
modal.find('.modal-body #booking-start-date').val(moment(date).format('lll'));
modal.find('.modal-body #booking-end-date').val(moment(date).add(1, 'day').format('lll'));
modal.find('.modal-body #booking-notes').val('');
if (bookingId !== '') {
modal.find('.modal-body form').attr('action', "{{ route('bookings.update', '') }}/" + bookingId);
modal.find('.modal-body #booking-notes').val(button.data('notes'));
} else {
modal.find('.modal-body form').attr('action', "{{ route('bookings.store') }}");
}
});
});
</script>
@endsection
