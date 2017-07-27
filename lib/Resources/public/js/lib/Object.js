ag.ns("ag.api");

(function() {

var apiObject = function(objectName, defaultValues)
{
    defaultValues = defaultValues || {};

    if (!apiObject.list[objectName])
        throw new ag.api.ApiError("Unknown object name: " + objectName);

    var objectMeta = apiObject.list[objectName],
        propMeta = objectMeta.props,
        values = {},
        self = this;

    Object.keys(propMeta).forEach(function(prop){
        if (defaultValues[prop] !== undefined)
            values[prop] = defaultValues[prop];
        else if (propMeta[prop].default !== undefined)
            values[prop] = propMeta[prop].default;
        else
            values[prop] = null;

        Object.defineProperty(self, prop, {
            get: function() { return values[prop]; },
            set: function(value) { values[prop] = value; },
            enumerable: true
        });
    });

    Object.defineProperty(this, "_name", { value: objectName });
    Object.defineProperty(this, "_meta", { value: objectMeta });
};

apiObject.prototype.getName = function()
{
    return this._name;
};

apiObject.prototype.getObjectMeta = function()
{
    // NOTE: This refers to metadata of the object itself.
    // In most cases, this is empty (undefined).
    return this._meta.meta;
};

apiObject.prototype.getPropMeta = function(propName)
{
    if (this._meta.props[propName] === undefined)
        throw new ag.api.ApiError("Object `" + this._name + "` does not have a `" + propName + "` property.");

    return this._meta.props[propName];
};

apiObject.prototype.isScalar = function()
{
    return this._meta.props._ && Object.keys(this._meta.props).length === 1;
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

ag.api.Object = apiObject;

})();
