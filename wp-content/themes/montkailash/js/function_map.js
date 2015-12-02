var map;
jQuery(document).ready(function () {
    map = new GMaps({
        div: '#map',
        lat: 48.856614,
        lng: 2.3522219000000177,
        zoom: 13
    });
    map.addMarker({
        lat: 48.856614,
        lng: 2.3522219000000177,
        title: 'Paris 2eme',
        infoWindow: {
            content: '<img src="/wp-content/themes/montkailash/img/spa-logo.png" width="150px"><h5>16, Rue Saint-Marc,<br>75002 Paris</h5>'
        }
    });
    map.addMarker({
        lat: 48.8489983,
        lng: 2.3193942000000334,
        title: 'Paris 7eme',
        infoWindow: {
            content: '<img src="/wp-content/themes/montkailash/img/spa-logo.png" width="150px"><h5>19 Rue Pierre Leroux,<br>75007 Paris</h5>'
        }
    });
});
