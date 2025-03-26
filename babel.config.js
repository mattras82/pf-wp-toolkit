module.exports = {
    "presets": [
        [
            "@babel/preset-env",
            {
                "modules": false,
                "corejs": 3,
                "useBuiltIns": "usage"
            }
        ]
    ],
    "plugins": [
        "@babel/plugin-transform-class-properties"
    ],
    "comments": true
};
