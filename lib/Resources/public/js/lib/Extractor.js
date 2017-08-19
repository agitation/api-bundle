ag.ns("ag.api");

(function(){
var entityReferencePattern = /#e#:[0-9]+/,

expandEntities = function(value, objName, entityList)
{
    var newValue = value;

    if (value instanceof Array)
    {
        newValue = [];

        $.each(value, function(k, v){
            newValue.push(expandEntities(v, objName, entityList));
        });
    }
    else if (value instanceof Object)
    {
        if (objName)
        {
            newValue = new ag.api.Object(objName);

            $.each(value, function(prop, val){
                var meta = newValue.getPropMeta(prop);
                newValue[prop] = expandEntities(val, meta["class"] || null, entityList);
            });
        }
        else
        {
            newValue = {};

            $.each(value, function(k, v){
                newValue[k] = expandEntities(v, null, entityList);
            });
        }
    }
    else if (typeof(value) === "string" && value.match(entityReferencePattern))
    {
        newValue = expandEntities(entityList[value], objName, entityList);
    }

    return newValue;
};

ag.api.extract = function(responseObjectName, payload, entityList)
{
    if (responseObjectName.substr(-2) === "[]")
        responseObjectName = responseObjectName.substr(0, responseObjectName.length - 2);

    return expandEntities(payload, responseObjectName, entityList);
};

})();
