$(document).ready(function () {
/*
    $('#myModal').on('shown.bs.modal', function () {
        $('#new-input-hash').focus()
    });*/

    $('#btn-add-article').on('click', function (e) {
        var form = $('#new-dictionary-article').serialize();
        console.log(form);
        $.ajax({
            method: 'POST',
            url: '/dictionary/add_article',
            data: form
        }).done(function( msg ) {
            console.log( msg );
        }).fail(function( jqXHR, textStatus ) {
            console.warn( "Request failed: " + textStatus, jqXHR );
        }).always(function(){
            window.location.reload();
            $('#dictionary-item-edit-modal').modal('hide');
        });
    })

    $('.btn.article-delete').on('click', function( e ){
        $.ajax({
            method: 'POST',
            url: '/dictionary/delete_article',
            data: {id: this.dataset.id}
        }).done(function( msg ) {
            console.log( msg );
        }).fail(function( jqXHR, textStatus ) {
            console.warn( "Request failed: " + textStatus, jqXHR );
        }).always(function(){
            window.location.reload();
        });
    });

});