/*jslint white: true */
/*global Agit */

Agit.Object.register({
    "common.v1/Null": [],
    "common.v1/ObjectList": {
        "itemList": {
            "type": "polymorphic",
            "nullable": true,
            "default": []
        }
    },
    "common.v1/String": {
        "_": {
            "type": "string"
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
