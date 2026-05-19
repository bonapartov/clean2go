$(document).ready(function () {
    $('#myModal').on('shown.bs.modal', function () {

        $('#filterService').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#filterServicemen').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#provider_type').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#filterStatus').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#filterType').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#advertisementScreen').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#advertisementScreen').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

        $('#advertisementScreen').select2({
            dropdownParent: $('#myModal'),
            width: '100%'
        });

    });
});
