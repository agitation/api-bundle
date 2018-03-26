ag.ns("ag.api");

(function() {

var ApiObject = function(objectName, defaultValues)
{
    defaultValues = defaultValues || {};

    var self = this,
        meta = ApiObject.getMeta(objectName),
        values = {};


    Object.keys(meta.props).forEach(function(prop){
        if (defaultValues[prop] !== undefined)
            values[prop] = defaultValues[prop];
        else if (meta.props[prop].default !== undefined)
            values[prop] = meta.props[prop].default;
        else
            values[prop] = null;

        Object.defineProperty(self, prop, {
            get: function() { return values[prop]; },
            set: function(value) { values[prop] = value; },
            enumerable: true
        });
    });

    Object.defineProperty(this, "_name", { value: objectName });
};

ApiObject.prototype.getName = function()
{
    return this._name;
};

ApiObject.prototype.getMeta = function()
{
    return ApiObject.getMeta(this.getName());
};

ApiObject.prototype.getPropMeta = function(propName)
{
    return ApiObject.getPropMeta(this.getName(), propName);
};

//
// static methods/props
//

ApiObject.SIMPLE_TYPES = ["string", "integer", "null", "boolean"];

ApiObject.isSimpleType = function(objectName)
{
    return ApiObject.SIMPLE_TYPES.indexOf(objectName) >= 0;
};

ApiObject.list = {};

ApiObject.register = function(objects)
{
    Object.keys(objects).map(function(key){
        ApiObject.list[key] = objects[key];
    });
};

ApiObject.exists = function(objectName)
{
    return !!ApiObject.list[objectName];
};

ApiObject.getMeta = function(objectName)
{
    if (!ApiObject.list[objectName])
        throw new Error("Unknown object name: " + objectName);

    return ApiObject.list[objectName];
};

ApiObject.getPropMeta = function(objectName, propName)
{
    var meta = ApiObject.getMeta(objectName);

    if (meta.props[propName] === undefined)
        throw new Error("Object `" + objectName + "` does not have a `" + propName + "` property.");

    return meta.props[propName];
};


ag.api.Object = ApiObject;

})();
