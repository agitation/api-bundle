ag.ns("ag.api");

(function() {

var apiObject = function(objectName, defaultValues)
{
    defaultValues = defaultValues || {};

    var self = this,
        meta = apiObject.getMeta(objectName),
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

apiObject.prototype.getName = function()
{
    return this._name;
};

apiObject.prototype.getMeta = function()
{
    return apiObject.getMeta(this.getName());
};

apiObject.prototype.getPropMeta = function(propName)
{
    return apiObject.getPropMeta(this.getName(), propName);
};

apiObject.prototype.isScalar = function()
{
    return apiObject.isScalar(this.getName());
};

//
// static methods/props
//

apiObject.list = {};

apiObject.register = function(objects)
{
    Object.keys(objects).map(function(key){
        apiObject.list[key] = objects[key];
    });
};

apiObject.exists = function(objectName)
{
    return !!apiObject.list[objectName];
};

apiObject.getMeta = function(objectName)
{
    if (!apiObject.list[objectName])
        throw new Error("Unknown object name: " + objectName);

    return apiObject.list[objectName];
};

apiObject.getPropMeta = function(objectName, propName)
{
    var meta = apiObject.getMeta(objectName);

    if (meta.props[propName] === undefined)
        throw new Error("Object `" + objectName + "` does not have a `" + propName + "` property.");

    return meta.props[propName];
};

apiObject.isScalar = function(objectName)
{
    var meta = apiObject.getMeta(objectName);
    return !!(meta.props._ && Object.keys(meta.props).length === 1);
};


ag.api.Object = apiObject;

})();
