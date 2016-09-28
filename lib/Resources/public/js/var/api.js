ag.api.Object.register({
    "common.v1/Time": {
        "props": {
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
        }
    },
    "common.v1/DateTime": {
        "props": {
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
        }
    },
    "common.v1/Null": {
        "props": []
    },
    "common.v1/String": {
        "props": {
            "_": {
                "type": "string"
            }
        }
    },
    "common.v1/Month": {
        "props": {
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
        }
    },
    "common.v1/Response": {
        "props": {
            "payload": {
                "type": "polymorphic",
                "nullable": true
            },
            "entityList": {
                "type": "polymorphic",
                "nullable": true,
                "default": []
            }
        }
    },
    "common.v1/Location": {
        "props": {
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
        }
    },
    "common.v1/Money": {
        "props": {
            "amount": {
                "type": "number",
                "allowFloat": true
            },
            "currency": {
                "type": "string",
                "minLength": 3,
                "maxLength": 3
            }
        }
    },
    "common.v1/Date": {
        "props": {
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
        }
    },
    "common.v1/Period": {
        "props": {
            "from": {
                "type": "object",
                "class": "common.v1/Date"
            },
            "until": {
                "type": "object",
                "class": "common.v1/Date"
            }
        }
    },
    "common.v1/Integer": {
        "props": {
            "_": {
                "type": "number"
            }
        }
    }
});
