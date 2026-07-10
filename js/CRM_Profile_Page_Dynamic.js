CRM.$(function($) {
  // The relevant favorite icon/control.
  var elFavoriteControl = $('.nmregistry-favorite');

  // On-click handler for 'favorite' icon/control.
  var favoriteClick = function favoriteClick(){

    var action, args;

    if(elFavoriteControl.data('is-favorited')) {
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
      elFavoriteControl.removeClass('fa-beat-fade');
      // handle failure?
    });

    // Throb the control for visual feedback.
    elFavoriteControl.addClass('fa-beat-fade');
  };

  // Handle results for favorite/un-favorite api call.
  var handleResults = function handleResults(action) {
    // Un-throb the control.
    elFavoriteControl.removeClass('fa-beat-fade');

    var isFavorite;
    if(action == 'createOwn') {
      // Results are for 'favorite', so mark as favorited.
      elFavoriteControl.data('is-favorited', 1);
      elFavoriteControl.addClass('fa-solid');
      elFavoriteControl.removeClass('fa-regular');
      isFavorite = true;
    }
    else if(action == 'deleteOwn') {
      // Results are for 'un-favorite', so mark as NOT favorited.
      elFavoriteControl.data('is-favorited', 0);
      elFavoriteControl.addClass('fa-regular');
      elFavoriteControl.removeClass('fa-solid');
      isFavorite = false;
    }

    notifyParent(isFavorite);
  }

  // Notifier: send a message to the parent page about the status change (assuming we're in an iframe)
  var notifyParent = function notifyParent(isFavorite) {
    window.parent.postMessage({
        type: 'isFavoriteChanged',
        providerCid: CRM.vars.nmregistry.providerCid,
        isFavorite: isFavorite
    }, window.location.origin);
  }

  // Define on-click handler for 'favorite' icon/control.
  elFavoriteControl.click(favoriteClick);
});