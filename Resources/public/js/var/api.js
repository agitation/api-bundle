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
        "success": {
            "type": "boolean"
        },
        "messageList": {
            "type": "objectlist",
            "class": "common.v1/Message",
            "default": []
        },
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
    "common.v1/Message": {
        "type": {
            "type": "string",
            "values": [
                "info",
                "success",
                "warning",
                "error"
            ]
        },
        "code": {
            "type": "string",
            "nullable": true
        },
        "text": {
            "type": "string"
        }
    },
    "common.v1/Integer": {
        "_": {
            "type": "number"
        }
    }
});
