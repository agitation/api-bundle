ag.ns("ag.api");

ag.api.Object = (function() {
    var
        factoryProto =
        {
            getName : function()
            {
                return this._name;
            },

            getObjectMeta : function()
            {
                // NOTE: This refers to metadata of the object itself.
                // In most cases, this is empty (undefined).
                return this._meta.meta;
            },

            getPropMeta : function(propName)
            {
                if (this._meta.props[propName] === undefined)
                    throw new ag.api.ApiError("Object `" + this._name + "` does not have a `" + propName + "` property.");

                return this._meta.props[propName];
            },

            isScalar : function()
            {
                return this._meta.props._ && Object.keys(this._meta.props).length === 1;
            }
        };

    return function(objectName, defaultValues)
    {
        defaultValues = defaultValues || {};

        var
            objectMeta = ag.api.Object.list[objectName],
            values = {},
            object = Object.create(factoryProto);

        if (objectMeta === undefined)
            throw new ag.api.ApiError("Object `" + objectName + "` does not exist.");

        Object.keys(objectMeta).forEach(function(prop){
            values[prop] = (defaultValues[prop] !== undefined)
                ? defaultValues[prop]
                : objectMeta[prop].default !== undefined
                    ? objectMeta[prop].default
                    : null;

            Object.defineProperty(object, prop, {
                get: function() { return values[prop]; },
                set: function(value) { values[prop] = value; },
                enumerable: true
            });
        });

        Object.defineProperty(object, "_name", { value: objectName });
        Object.defineProperty(object, "_meta", { value: objectMeta });

        return object;
    };
})();

ag.api.Object.list = {};

ag.api.Object.register = function(objects)
{
    Object.keys(objects).map(function(key){
        ag.api.Object.list[key] = objects[key];
    });
};

ag.api.Object.exists = function(objectName)
{
    return !!ag.api.Object.list[objectName];
};
