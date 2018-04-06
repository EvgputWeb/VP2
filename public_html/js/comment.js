$('body').on('click', 'a[href*=commentfile]', function(e) {
    e.preventDefault();

    swal('Комментарии');

    const {value: text} = await
    swal({
        input: 'textarea',
        inputPlaceholder: 'Комментарии к фотографии',
        showCancelButton: true
    });

    if (text) {
        swal(text)
    }

});
