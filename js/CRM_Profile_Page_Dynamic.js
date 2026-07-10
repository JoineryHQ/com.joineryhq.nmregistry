CRM.$(function($) {
  // On-click handler for 'favorite' icon/control.
  var favoriteClick = function favoriteClick(){

    var action, args;
    if($('#nmregistry-favorite').data('is-favorited')) {
      // If we're already favorited, this click will un-favorite.
      action = 'deleteOwn';
      args = {
        where: [["favorite_contact_id", "=", CRM.vars.nmregistry.providerCid]]
      }
    }
    else {
      // If we're NOT already favorited, this click will favorite.
      action = 'createOwn';
      args = {
        values: {"favorite_contact_id":CRM.vars.nmregistry.providerCid}
      }
    }

    // Send the api call to favorite/un-favorite.
    CRM.api4('NmregistryFavorite', action, args).then(function(results) {
      handleResults(action);
    }, function(failure) {
      // Un-throb the control.
      $('#nmregistry-favorite').removeClass('fa-beat-fade');
      // handle failure?
    });

    // Throb the control.
    $('#nmregistry-favorite').addClass('fa-beat-fade');
  };

  // Results handler for favorite/un-favorite api call.
  var handleResults = function handleResults(action) {
    // Un-throb the control.
    $('#nmregistry-favorite').removeClass('fa-beat-fade');

    if(action == 'createOwn') {
      // Results are for 'favorite', so mark as favorited.
      $('#nmregistry-favorite').data('is-favorited', 1);
      $('#nmregistry-favorite').addClass('fa-solid');
      $('#nmregistry-favorite').removeClass('fa-regular');
    }
    else if(action == 'deleteOwn') {
      // Results are for 'un-favorite', so mark as NOT favorited.
      $('#nmregistry-favorite').data('is-favorited', 0);
      $('#nmregistry-favorite').addClass('fa-regular');
      $('#nmregistry-favorite').removeClass('fa-solid');
    }
  }

  // Define on-click handler for 'favorite' icon/control.
  $('#nmregistry-favorite').click(favoriteClick);
});