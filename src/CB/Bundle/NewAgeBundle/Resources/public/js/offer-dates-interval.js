define(['jquery','routing', 'orolocale/js/formatter/datetime'], function ($, routing, datetimeFormater) {

    /**
     * @export cbnewage/js/offer-dates-interval
     */
    return {
        init: function () {

            var addInterval = function () {
                var offerStart = $("[id^='date_selector_cb_newage_offer_form_start']");
                var offerEnd = $("[id^='date_selector_cb_newage_offer_form_end']");

                if (offerStart.val() && offerStart.length) {
                    var xDate = offerStart.datepicker('getDate', '+1d');
                    xDate.setDate(xDate.getDate() + 7);

                    offerEnd.datepicker('setDate', xDate);
                    offerEnd.change();
                } else {
                    offerEnd.datepicker('setDate', null);
                }
            };

            $("[id^='cb_newage_offer_form_start']").closest('form').on('change', "[id^='date_selector_cb_newage_offer_form_start']", addInterval);
        }
    };
});