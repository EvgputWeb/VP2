$('body').on('click', 'a[href*=deletefile]', function(e){
    e.preventDefault();

    var filename = $(this).data('filename');

    swal({
        title: 'Хотите удалить этот файл?',
        text: 'Отменить операцию будет невозможно',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        /*cancelButtonColor: '#d33',*/
        confirmButtonText: 'Да, хочу удалить!',
        cancelButtonText: 'Отмена'
    }).then((result) => {
        // Пользователь подтвердил своё намерение - удалить!
        if (result.value) {
            $.ajax({
                url: '/admin/deletefile',
                type: 'POST',
                dataType: 'json',
                data: {
                    filename: filename
                }
            })
                .done(function(data) {
                    if (data.result == 'success') {
                        window.location.reload();
                        /*
                        // Получилось
                        swal({
                            title: 'Успешно!',
                            text: 'Файл удален',
                            type: 'success',
                            confirmButtonColor: '#619c34',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            // Перезагружаем страницу
                            window.location.reload();
                        })
                        */
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

        }
    });


});
