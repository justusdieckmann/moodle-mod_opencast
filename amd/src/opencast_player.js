define(['mod_opencast/jquery', 'jqueryui', 'mod_opencast/swfobject', 'mod_opencast/base',
        'mod_opencast/lunr', 'mod_opencast/paella', 'mod_opencast/opencast_to_paella_converter'],
    function($, jqui, swfobject, base, lunr, paella, OpencastToPaellaConverter) {

        var wwwroot = M.cfg.wwwroot;

        function initManage(episode) {
            $("body").ready(() => {
                $("body").append('<div id="playerContainer" style="display:block;width:100%"></div>');
                paella.lazyLoad('playerContainer', {
                    'configUrl': wwwroot + '/mod/opencast/paella/player/config/config.json',
                    loadVideo: function() {
                        return new Promise((resolve) => {
                            let data = new OpencastToPaellaConverter().convertToDataJson(episode);
                            resolve(data);
                        });
                    }
                });
            });
        }

        return {
            init: initManage
        };
    });