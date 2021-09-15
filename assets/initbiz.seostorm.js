(function (win, doc) {
    initbiz = win.initbiz || {};
    initbiz.seostorm = {
        charCountHandler: function (target) {
            $target = $(target);
            let $helpBlock = $('#seostorm-' + $target.data("seo"));
            if (!$helpBlock.length) {
                $target.after('<div id="seostorm-' + $target.data("seo") + '" class="help-block"</div>');
                $helpBlock = $target.next(".help-block");
            }
            let isTwig = /\{{2}.*\}{2}/.test($target.val());
            let count = isTwig ? "unrechable" : $target.val().length;

            let min = $target.data("min");
            let max = $target.data("max");

            $helpBlock.html(`
        Character count: <b>${count}</b>
        |
        recommended: ${min} - ${max}
      `);

            let $number = $helpBlock.find("b");

            if (count < max && count > min) {
                $number.css({ color: "green" });
            } else if (isTwig) {
                $number.css({ color: "orange" });
            } else {
                $number.css({ color: "red" });
            }
        },
    };

    var listeners = [],
        doc = win.document,
        MutationObserver = win.MutationObserver || win.WebKitMutationObserver,
        observer;

    function ready(selector, fn) {
        // Store the selector and callback to be monitored
        listeners.push({
            selector: selector,
            fn: fn,
        });
        if (!observer) {
            // Watch for changes in the document
            observer = new MutationObserver(check);
            observer.observe(doc.documentElement, {
                childList: true,
                subtree: true,
            });
        }
        // Check if the element is currently in the DOM
        check();
    }

    function check() {
        // Check the DOM for elements matching a stored selector
        for (
            var i = 0, len = listeners.length, listener, elements;
            i < len;
            i++
        ) {
            listener = listeners[i];
            // Query for elements matching the specified selector
            elements = doc.querySelectorAll(listener.selector);
            for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
                element = elements[j];
                // Make sure the callback isn't invoked with the
                // same element more than once
                if (!element.ready) {
                    element.ready = true;
                    // Invoke the callback with the element
                    listener.fn.call(element, element);
                }
            }
        }
    }

    // Expose stuff
    win.ready = ready;
    win.initbiz = initbiz;

    // execute listeners
    win.ready("[data-counter]", (el) => {
        initbiz.seostorm.charCountHandler(el);
        el.oninput = (event) => initbiz.seostorm.charCountHandler(el);
    });
})(window, document);
