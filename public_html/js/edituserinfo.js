function htmlUnescape(str){
    return str
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'")
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&');
}


$('body').on('click', 'a[href*=editselfdata]', function(e) {
    e.preventDefault();

    var userId = $(this).data('id');
    var name = $(".admin-user-info-data .user-info-name").text();
    var age = $(".admin-user-info-data .user-info-age").data('age');
    var descr = $(".admin-user-info-data .user-info-description").html();

    var regex = /<br\s*[\/]?>/gi;
    descr = htmlUnescape(descr.replace(regex, "\n"));

    //console.log(descr);
    //return;

    swal({
        title: "Информация о пользователе",
        showCancelButton: true,
        confirmButtonColor: '#619c34',
        confirmButtonText: 'Сохранить',
        cancelButtonText: 'Отмена',
        html:
        '<input id="swal-input-name" class="swal2-input">' +
        '<input id="swal-input-age" class="swal2-input">' +
        '<textarea id="swal-input-descr" class="swal2-textarea" rows="2">',
        preConfirm: function () {
            return new Promise(function (resolve) {
                resolve([
                    $('#swal-input-name').val(),
                    $('#swal-input-age').val(),
                    $('#swal-input-descr').val()
                ])
            })
        },
        onOpen: function () {
            $('#swal-input-name').val(name).focus();
            $('#swal-input-age').val(age);
            $('#swal-input-descr').val(descr);
        }
    }).then(
        // Отсылаем новые данные в обработчик
        result => {
            if (result.value) {
                $.ajax({
                    url: '/admin/editselfdata',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        userId: userId,
                        newName: result.value[0],
                        newAge: result.value[1],
                        newDescription: result.value[2]
                    }
                })
                    .done(function (data) {
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
                    .fail(function () {
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
        }
    );

});
