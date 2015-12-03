/*jslint white: true */
/*global Agit */

Agit.Endpoint.registerList({});
Agit.Object.registerList({
    "common.v1/Null": {},
    "common.v1/ObjectList": {
        "itemList": {
            "type": "polymorphic",
            "nullable": true,
            "name": "itemList",
            "default": {}
        }
    },
    "common.v1/String": {
        "value": {
            "type": "string",
            "name": "value"
        }
    },
    "common.v1/Response": {
        "success": {
            "type": "boolean",
            "name": "success"
        },
        "messageList": {
            "type": "objectlist",
            "class": "common.v1/Message",
            "name": "messageList",
            "default": {}
        },
        "payload": {
            "type": "polymorphic",
            "nullable": true,
            "name": "payload"
        },
        "entityList": {
            "type": "polymorphic",
            "nullable": true,
            "name": "entityList",
            "default": {}
        }
    },
    "common.v1/Message": {
        "type": {
            "type": "string",
            "values": {
                "0": "info",
                "1": "success",
                "2": "warning",
                "3": "error"
            },
            "name": "type"
        },
        "code": {
            "type": "string",
            "nullable": true,
            "name": "code"
        },
        "text": {
            "type": "string",
            "name": "Message Text"
        }
    },
    "common.v1/Integer": {
        "value": {
            "type": "number",
            "name": "value"
        }
    }
});