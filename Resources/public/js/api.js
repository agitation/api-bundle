/*jslint white: true */
/*global Agit */

Agit.Endpoint.registerList([]);
Agit.Object.registerList({
    "common.v1/ObjectList": {
        "itemList": {
            "type": "polymorphic",
            "nullable": true,
            "name": "itemList",
            "default": []
        }
    },
    "common.v1/String": {
        "value": {
            "type": "string",
            "values": null,
            "name": "value",
            "default": null
        }
    },
    "common.v1/Response": {
        "success": {
            "type": "boolean",
            "name": "success",
            "default": null
        },
        "messageList": {
            "type": "objectlist",
            "class": "common.v1/Message",
            "name": "messageList",
            "default": []
        },
        "payload": {
            "type": "polymorphic",
            "nullable": true,
            "name": "payload",
            "default": null
        },
        "entityList": {
            "type": "polymorphic",
            "nullable": true,
            "name": "entityList",
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
            ],
            "name": "type",
            "default": null
        },
        "code": {
            "type": "string",
            "values": null,
            "nullable": true,
            "name": "code",
            "default": null
        },
        "text": {
            "type": "string",
            "values": null,
            "name": "Message Text",
            "default": null
        }
    }
});