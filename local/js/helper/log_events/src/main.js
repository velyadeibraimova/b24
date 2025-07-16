const originalBXEvent = BX.onCustomEvent;
BX.onCustomEvent = function (eventObject, eventName, eventParams, secureParams) {
    const name = BX.type.isString(eventName)
        ? eventName
        : BX.type.isString(eventObject)
            ? eventObject
            : null;

    if (name) {
        console.log('%c Событие: ' + name, 'color: green; font-weight: bold');
        console.dir({eventObject, eventParams, secureParams});
    }

    return originalBXEvent.apply(this, arguments);
};
