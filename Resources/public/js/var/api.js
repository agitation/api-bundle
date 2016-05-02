agit.api.Object.register({
    "common.v1/Time": {
        "hour": {
            "type": "number",
            "minValue": 0,
            "maxValue": 23
        },
        "minute": {
            "type": "number",
            "minValue": 0,
            "maxValue": 59
        }
    },
    "common.v1/DateTime": {
        "day": {
            "type": "number",
            "minValue": 1,
            "maxValue": 31
        },
        "month": {
            "type": "number",
            "minValue": 1,
            "maxValue": 12
        },
        "year": {
            "type": "number",
            "minValue": 2000,
            "maxValue": 2100
        },
        "hour": {
            "type": "number",
            "minValue": 0,
            "maxValue": 23
        },
        "minute": {
            "type": "number",
            "minValue": 0,
            "maxValue": 59
        }
    },
    "common.v1/Null": [],
    "common.v1/String": {
        "_": {
            "type": "string"
        }
    },
    "common.v1/Month": {
        "month": {
            "type": "number",
            "minValue": 1,
            "maxValue": 12
        },
        "year": {
            "type": "number",
            "minValue": 2000,
            "maxValue": 2100
        }
    },
    "common.v1/Response": {
        "payload": {
            "type": "polymorphic",
            "nullable": true
        },
        "entityList": {
            "type": "polymorphic",
            "nullable": true,
            "default": []
        }
    },
    "common.v1/Location": {
        "lat": {
            "type": "number",
            "allowFloat": true,
            "minValue": -90,
            "maxValue": 90
        },
        "lon": {
            "type": "number",
            "allowFloat": true,
            "minValue": -180,
            "maxValue": 180
        }
    },
    "common.v1/Date": {
        "day": {
            "type": "number",
            "minValue": 1,
            "maxValue": 31
        },
        "month": {
            "type": "number",
            "minValue": 1,
            "maxValue": 12
        },
        "year": {
            "type": "number",
            "minValue": 2000,
            "maxValue": 2100
        }
    },
    "common.v1/Period": {
        "from": {
            "type": "object",
            "class": "common.v1/Date"
        },
        "until": {
            "type": "object",
            "class": "common.v1/Date"
        }
    },
    "common.v1/Integer": {
        "_": {
            "type": "number"
        }
    }
});
