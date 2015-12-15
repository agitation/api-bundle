Agit.Object = (function() {
    var
        factoryProto =
        {
            getName : function()
            {
                return this._name;
            },

            getMeta : function()
            {
                return this._meta;
            },

            getPropMeta : function(propName)
            {
                return this._meta[propName];
            },

            isScalar : function()
            {
                return this._meta._ && Object.keys(this._meta).length === 1;
            }
        };

    return function(objectName, defaultValues)
    {
        defaultValues = defaultValues || {};

        var
            objectMeta = Agit.Object.list[objectName],
            values = {},
            object = Object.create(factoryProto);

        if (objectMeta === undefined)
            throw new ApiError("Object `" + objectName + "` does not exist.");

        Object.keys(objectMeta).forEach(function(prop){
            values[prop] = (defaultValues[prop] !== undefined)
                ? defaultValues[prop]
                : objectMeta[prop].default || null;

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

Agit.Object.list = {};

Agit.Object.register = function(objects)
{
    Object.keys(objects).map(function(key){
        Agit.Object.list[key] = objects[key];
    });
};

Agit.Object.exists = function(objectName)
{
    return !!Agit.Object.list[objectName];
};
