function htmlUnescape(str){
    return str
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'")
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&');
}


$('body').on('click', 'a[href*=commentfile]', function(e) {
    e.preventDefault();

    var filename = $(this).data('filename');
    var selector = ".comment-to-file[data-filename='"+filename+"']";
    var curcomment = $(selector).html();

    var regex = /<br\s*[\/]?>/gi;
    curcomment = htmlUnescape(curcomment.replace(regex, "\n"));

    swal({
        input: 'textarea',
        inputPlaceholder: 'Комментарии к фотографии',
        inputValue: htmlUnescape(curcomment),
        showCancelButton: true,
        confirmButtonColor: '#619c34',
        confirmButtonText: 'Сохранить',
        cancelButtonText: 'Отмена'
    }).then(
        result => {
            if (result.value) {
                $.ajax({
                    url: '/admin/commentfile',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        filename: filename,
                        comment: result.value
                    }
                })
                    .done(function(data) {
                        if (data.result == 'success') {
                            window.location.reload();
                        } else {
                            // Ошибка - выдаём сообщение
                            swal({
                                type: 'error',
                                title: 'Произошла ошибка',
                                text: data.errorMessage
                            }).then((result) => {
                                // Перезагружаем страницу
                                window.location.reload();
                            })
                        }
                    })
                    .fail(function() {
                        // Здесь просто сообщение об ошибке
                        swal({
                            type: 'error',
                            title: 'Упс...',
                            text: 'Что-то пошло не так'
                        }).then((result) => {
                            // Перезагружаем страницу
                            window.location.reload();
                        })
                    });
        } else {
            //console.log(`dialog was dismissed by ${result.dismiss}`)
        }
    });


});
