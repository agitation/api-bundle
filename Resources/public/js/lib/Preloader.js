ag.ns("ag.api");

ag.api.Preloader = function()
{
    var
        callbacks = { entities : {}, settings : {} },
        data = { entities : {}, settings : {} },

        regCounter = 0, // counts the number of callbacks registered
        triggerCounter = 0, // counts the number of callbacks triggered

        finishCallback, // callback passed to this.run()
        loadingFinished = false,

        load = function(type, name, callback)
        {
            if (loadingFinished)
            {
                // we are able to return loaded items to non-subscribed callers even after loading,
                // but only for entities that had been registered
                callback(data[type][name]);
            }
            else
            {
                if (!callbacks[type][name])
                    callbacks[type][name] = [];

                callback && callbacks[type][name].push(callback) && ++regCounter;
            }
        },

        callbackTriggered = function()
        {
            ++triggerCounter === regCounter && finishCallback && finishCallback();
        };

    this.loadEntity = function(name, callback)
    {
        load("entities", name, callback);
    };

    this.loadSetting = function(name, callback)
    {
        load("settings", name, callback);
    };

    this.run = function(callback)
    {
        var apiService = ag.srv("api");

        finishCallback = callback;

        Object.keys(callbacks.settings).length && apiService.doCall(
            "setting.v1/Settings.load",
            Object.keys(callbacks.settings),
            function(settingList){
                data.settings = {};

                settingList.forEach(function(setting){
                    data.settings[setting.id] = setting.value;

                    callbacks.settings[setting.id].forEach(function(sCallback){
                        sCallback(setting.value);
                        callbackTriggered();
                    });
                });
            });

        // entities
        Object.keys(callbacks.entities).forEach(function(name){
            var endpoint = new ag.api.Endpoint(name + ".search");

            data.entities = {};

            apiService.doCall(
                endpoint,
                new ag.api.Object(endpoint.getRequest()),
                function(entityList){
                    data.entities[name] = entityList;

                    callbacks.entities[name].forEach(function(eCallback){
                        eCallback(entityList);
                        callbackTriggered();
                    });
                });
        });

        if (Object.keys(callbacks.entities).length + Object.keys(callbacks.settings).length === 0)
            callback();

        loadingFinished = true;
    };
};
