$(document).ready(function(e) {
    $('.datatable').DataTable();
    console.log(1)
    // $('#phone').mask('+(000) 00 000 000');

    // $('#number').mask('0000/0000', {
    //     reverse: true,
    //     onKeyPress: function(cep, e, field, options) {
    //         if (cep.length > 4) {
    //             return $(field).mask('0'.repeat(cep.length - 4) + '/0000', options);
    //         }
    //
    //         $(field).mask('0000/0000', options)
    //     }
    // });
})
