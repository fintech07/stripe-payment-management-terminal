let stripe, elements, cardNumberElement, cardCvcElement, cardExpiryElement;
var total = 0.0;

var app = {

    /*
     * init is where we initialize everything
     */
    init: function () {
        this.addValidation();
        this.bootstrap();
        this.tableSearch();
        this.paymentPage();
        this.checkNotification();
    },

    /******************************************************
     *
     ******************************************************/

    /*
   * setup our payment page
   */
    paymentPage: function () {

        $('input:text:visible:first').focus();

        // show hide the recurring notice
        $('input[name="payment_type"]').on('change', function () {
            var type = $('input[name="payment_type"]:checked').val();
            if (type == 'recurring') {
                $('.recurring-alert').show();

                var enableSubscriptions = $('.enable-subscriptions').val();

                if (enableSubscriptions == 'stripe_only') {
                    $('input[name="payment_method"][value="creditcard"]').click();
                    $('input[name="payment_method"][value="paypal"]').prop('disabled', true);
                }
                if (enableSubscriptions == 'paypal_only') {
                    $('input[name="payment_method"][value="paypal"]').click();
                    $('input[name="payment_method"][value="creditcard"]').prop('disabled', true);
                }


            } else {
                $('.recurring-alert').hide();
                $('input[name="payment_method"]').prop('disabled', false);
            }
        });

        // show hide the proper payment details
        $('input[name="payment_method"]').on('change', function () {
            var method = $('input[name="payment_method"]:checked').val();
            $('.stripe-length-text, .paypal-length-text').hide();
            if (method == 'paypal') {
                $('.paypal-content').show();
                $('.creditcard-content').hide();
                $('.paypal-length-text').show();
            } else {
                $('.paypal-content').hide();
                $('.creditcard-content').show();
                $('.stripe-length-text').show();
            }
        });

        // show hide the recurring notice
        $('input[name="name"]').on('blur', function () {
            $('input[data-stripe="name"]').val($(this).val());
        });

        // show proper card type image on keyup
        $('.card-number').on('keyup', function () {
            var number = $(this).val();
            var type = app.getCardType(number);
            $('.card-type-image').removeClass('visa mastercard amex discover none').addClass(type);
        });

        var updateTotal = function (total) {
            if (!isNaN(total)) {
                $('.submit-button .total span').html(total);
                $('.submit-button .total').css('display', 'block');
            } else {
                $('.submit-button .total').css('display', 'none');
            }
        }

        // show total amount on button
        $('select[name="item_id"]').on('change', function () {
            total = parseFloat($('select[name="item_id"] option:selected').attr('data-price')).toFixed(2);
            updateTotal(total);
        });
        $('input[name="amount"]').on('blur', function () {
            total = parseFloat($(this).val()).toFixed(2);
            updateTotal(total);
        });

        // update paypal form on paypal button click
        $('.paypal-button').on('click', function (e) {
            e.preventDefault();
            if ($('#order_form').valid()) {
                // set button to loading
                $('.paypal-button').button('loading');

                // get amount and description
                var amount = 0;
                var description = '';
                if ($('#order_form select[name="item_id"]').length) {
                    amount = $('#order_form select[name="item_id"] option:selected').attr('data-price');
                    description = $('#order_form select[name="item_id"] option:selected').attr('data-name');
                } else if ($('#order_form input[name="amount"]').length) {
                    amount = $('#order_form input[name="amount"]').val();
                    description = $('#order_form textarea[name="description"]').val();
                    description = description ? description : 'PayPal Payment';
                }
                // serialize our form data
                var formData = $('#order_form').serialize();
                // remove undeeded data from string
                formData = formData.replace(/(&?amount=[^&]*|&?description=[^&]*|&?action=[^&]*|&?payment_type=[^&]*|&?payment_method=[^&]*)/g, '');
                formData = formData.replace(/^&/, '');
                // add form data to custom input field
                $('.paypal-form input[name="custom"]').val(formData);


                // only update values if we dont' have invoice
                if (!$('input[name="invoice_id"]').length) {
                    if ($('input[name="payment_type"]:checked').val() == 'recurring') {
                        $('#paypal_form_recurring input[name="a3"]').val(amount);
                        $('#paypal_form_recurring input[name="item_name"]').val(description);
                    } else {
                        $('#paypal_form_one_time input[name="amount"]').val(amount);
                        $('#paypal_form_one_time input[name="item_name"]').val(description);
                    }
                }

                // submit proper form now
                if ($('input[name="payment_type"]:checked').val() == 'recurring') {
                    $('#paypal_form_recurring').submit();
                } else {
                    $('#paypal_form_one_time').submit();
                }
            }
        });

        // show success message on paypal success return
        if ($.jGet('status') == 'paypal_success') {
            app.response = 'Your PayPal payment has been received, you should receive a confirmation email shortly.';
            $('.submit-button').button('complete');
            setTimeout(function () {
                $('.submit-button').prop('disabled', true).removeClass('btn-primary').addClass('btn-default colorsuccess');
                app.showSuccess();
            }, 10);
        }
        if ($.jGet('status') == 'paypal_subscription_success') {
            app.response = 'Your PayPal subscription has been created successfully, you should receive a confirmation email shortly.';
            $('.submit-button').button('complete');
            setTimeout(function () {
                $('.submit-button').prop('disabled', true).removeClass('btn-primary').addClass('btn-default colorsuccess');
                app.showSuccess();
            }, 10);
        }

        // stripe elemets set up
        stripe = Stripe($('.publishable-key').val());

        elements = stripe.elements()

        cardNumberElement = elements.create('cardNumber')
        cardNumberElement.mount('#card-number-element', {});

        cardExpiryElement = elements.create('cardExpiry', {
        });
        cardExpiryElement.mount('#card-expiry-element');

        cardCvcElement = elements.create('cardCvc', {
        });
        cardCvcElement.mount('#card-cvc-element');
    },

    /*
    * setup our bootstrap functionality
    */
    bootstrap: function () {

        $('[data-toggle="tooltip"]').tooltip({ html: true });
        $('[data-toggle="popover"]').popover();

        if ($('.nav-tabs').length) {
            if ($.jGet('tab')) {
                $('.nav-tabs a[href="#' + $.jGet('tab') + '"]').tab('show');
            } else {
                $('.nav-tabs a:first').tab('show');
                if ($('.nav-tabs.hash-tabs').length) {
                    window.location.hash = '#tab=' + $('.nav-tabs.hash-tabs a:first').attr('href').substr(1);
                }
            }
            $('.hash-tabs a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
                window.location.hash = '#tab=' + e.target.hash.substr(1);
            });
        }

        // show last settings pane if it's set
        if (localStorage.activePill) {
            $('.nav-pill-control > li').removeClass('active');
            $('.nav-pill-control > li > a[href="' + localStorage.activePill + '"]').parent().addClass('active');
            $('.nav-pill-pane').hide();
            $(localStorage.activePill).show();
        }

        $('[data-hide]').on('click', function () {
            if ($(this).parent().hasClass('modal-header')) {
                $('#' + $(this).attr('data-hide')).modal('hide')
            } else {
                $(this).closest('.' + $(this).attr('data-hide')).hide();
            }
        });

        $('#add_item, #create_invoice').on('shown.bs.modal', function (e) {
            $(this).find('input:text:first').focus();
        });

        $('#edit_item').on('show.bs.modal', function (e) {
            var $tr = $(e.relatedTarget).closest('tr');
            $('#edit_item input[name="id"]').val($tr.attr('data-item-id'));
            $('#edit_item input[name="name"]').val($tr.attr('data-item-name'));
            $('#edit_item input[name="price"]').val($tr.attr('data-item-price'));
        });

        $('.datepicker').datepicker();

        $('.confirm-click').on('click', function (e) {
            e.preventDefault();
            $this = $(this);
            var text = $this.attr('data-text') ? $this.attr('data-text') : 'Yes, delete it!';
            swal({
                title: 'Are you sure?',
                text: 'This action cannot be undone.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonClass: 'btn-danger',
                confirmButtonText: text,
                closeOnConfirm: false
            }, function () {
                window.location = $this.attr('href');
            });

        });


        $('.nav-pill-control a').on('click', function (e) {
            e.preventDefault();
            $('.nav-pill-control > li').removeClass('active');
            $(this).parent().addClass('active');
            $('.nav-pill-pane').hide();
            $($(this).attr('href')).show();
            localStorage.activePill = $(this).attr('href');
        });

        $('.maxlength[maxlength]').maxlength({
            alwaysShow: false,
            threshold: 20,
            showCharsTyped: true,
            placement: 'bottom',
            warningClass: 'label label-success',
            limitReachedClass: 'label label-danger',
            separator: ' of ',
            validate: true
        });


    },

    /*
    * table search
    */
    tableSearch: function () {

        // live filter searching
        $('input.filter').on('keyup', function () {
            var $table = $(this).closest('.tab-pane').find('table');
            var rex = new RegExp($(this).val(), 'i');
            $table.find('tbody tr').hide();
            $table.find('tbody tr').filter(function () {
                return rex.test($(this).text());
            }).show();
            if ($table.find('tbody tr:visible').length === 0) {
                $table.find('tbody').next('tfoot').show();
            } else {
                $table.find('tbody').next('tfoot').hide();
            }
        });

    },


    /*
     * Add our jquery form validation here
     */
    addValidation: function () {

        $('form.validate').each(function () {
            $(this).validate({
                errorClass: 'validate-error control-label',
                validClass: 'validate-valid control-label',
                ignore: 'select:hidden:not(.selectpicker), input:hidden, textarea:hidden',
                errorPlacement: function (error, element) {
                    if (element.is('input:checkbox') || element.is('input:radio')) {
                        var lastElement = $('[name="' + element.attr('name') + '"]:last');
                        lastElement = element.parent().hasClass('icheck') ? lastElement.closest('label') : lastElement;
                        error.insertAfter(lastElement.parent().is('label') ? lastElement.parent() : lastElement);
                    } else {
                        if (element.closest('.input-group').length == 1) {
                            error.insertAfter(element.closest('.input-group'));
                        } else {
                            error.insertAfter(element);
                        }
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    if ($(element).closest('.form-group').length == 1) {
                        $(element).closest('.form-group').addClass('has-error');
                    } else {
                        $(element).addClass('validate-error');
                    }
                },
                unhighlight: function (element, errorClass, validClass) {
                    if ($(element).closest('.form-group').length == 1) {
                        $(element).closest('.form-group').removeClass('has-error');
                    } else {
                        $(element).removeClass('validate-error');
                    }
                },
                /*onkeyup: function(element, event) {
                    if ( !$(element).hasClass('check-email') ) {
                        $(element).valid();
                    }
                },*/
                onfocusout: function (element, event) {
                    if (!$(element).hasClass('check-email') && $(element).attr('aria-invalid')) {
                        $(element).valid();
                    }
                },
                invalidHandler: function (event, validator) {
                    // this fires if the form didn't pass validatation
                },
                submitHandler: function (form) {
                    app.submitForm(form);
                }
            });
        });

    },

    handleServerResponse: function (response) {
        if (response.error) {
            // Show error from server on payment form
            $button = $('.btn[data-loading-text]:visible:last');
            $button.button('reset')
            app.response = response.error;
            app.showError();

        } else if (response.requires_action) {
            // Use Stripe.js to handle required card action
            stripe.handleCardAction(
                response.payment_intent_client_secret
            ).then(function (result) {
                if (result.error) {
                    console.log('failed card action...')
                    app.response = result.error;
                    app.showError();
                    $button = $('.btn[data-loading-text]:visible:last');
                    $button.button('reset');
                } else {
                    // The card action has been handled
                    // The PaymentIntent can be confirmed again on the server
                    fetch('process.php?action=sca_payment', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            payment_type: $payment_type,
                            payment_intent_id: result.paymentIntent.id,
                            amount: $amount,
                            currency: $currency,
                            name: $name,
                            email: $email,
                            description: $description,
                            address: $address,
                            city: $city,
                            state: $state,
                            zip: $zip,
                            country: $country
                        })
                    }).then(function (confirmResult) {
                        return confirmResult.json();
                    }).then(app.handleServerResponse);
                }
            });
        } else {
            // Show success message
            app.response = 'Your recurring payment has been created successfully, you should receive a confirmation email shortly.';
            $button = $('.btn[data-loading-text]:visible:last');
            // reset the button state now
            $button.button('reset');
            // disable the current form and button
            $('#order_form').addClass('disabled');
            $button.button('complete');
            setTimeout(function () {
                $button.prop('disabled', true).removeClass('btn-primary').addClass('btn-default colorsuccess');
            }, 10);
            $('input[name="payment_method"]').prop('disabled', true);
            app.showSuccess();
        }
    },

    stripeTokenHandler: function (token) {
        fetch('process.php?action=sca_payment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                payment_type: $payment_type,
                stripeToken: token,
                amount: $amount,
                currency: $currency,
                name: $name,
                email: $email,
                description: $description,
                address: $address,
                city: $city,
                state: $state,
                zip: $zip,
                country: $country
            })
        }).then(function (result) {
            result.json().then(function (json) {
                if (json.error) {
                    app.response = json.error;
                    app.showError();
                } else {
                    if (json.status == 'requires_action') {
                        stripe.handleCardPayment(json.pi_secret).then(function (result) {
                            $button = $('.btn[data-loading-text]:visible:last');
                            $button.button('reset');
                            if (result.error) {
                                app.response = result.error;
                                app.showError();
                            } else {
                                app.response = result.status;
                                app.showSuccess();
                            }
                        })
                    }
                }
                return json;
            })
        });
    },

    /*
     * handle a form submission
     */
    submitForm: function (form) {
        $form = $(form);

        // prevent disabled form from submission
        if ($form.hasClass('disabled')) {
            return false;
        }

        // check for paypal method
        if ($('input[name="payment_method"]:checked').val() == 'paypal') {
            $('.paypal-button').click();
            return false;
        }

        // set our button to loading state
        $button = $form.find('.btn[data-loading-text]:visible:last');
        $button.button('loading');

        if ($form.attr('id') == 'order_form') {

            // hide errors first
            $('.error-alert').hide();

            $payment_type = $('input[name=payment_type]:checked').val();
            $name = $('input[name=name]').val();
            $description = $('textarea[name=description]').val()
            if (typeof($description) == 'undefined' ) {
                $description = $('select[name="item_id"] option:selected').attr('data-name');
            }
            $email = $('input[name=email]').val();
            $amount = total;
            $address = $('input[name=address]').val();
            $city = $('input[name=city]').val();
            $zip = $('input[name=zip]').val();
            $state = $('select[name=state] option:selected').val();
            $country = $('select[name=country] option:selected').val();
            $currency = $('input[name=currency_code]').val();

            stripe.createSource(cardNumberElement).then(function (result) {
                if (result.error) {
                    $button.button('reset')
                    app.response = result.error.message;
                    app.showError();
                } else {
                    source = result.source;
                }
            })

            if ($payment_type == 'one_time') {
                stripe.createPaymentMethod('card', cardNumberElement, {
                    billing_details: {
                        address: {
                            city: $city,
                            country: $country,
                            postal_code: $zip,
                            state: $state,
                        },
                        email: $email,
                        name: $name,
                    }
                }).then(function (result) {
                    if (result.error) {
                        $button.button('reset')
                        app.response = result.error.message;
                        app.showError();
                    } else {
                        fetch('process.php?action=sca_payment', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                payment_method_id: result.paymentMethod.id,
                                amount: $amount,
                                payment_type: $payment_type,
                                name: $name,
                                email: $email,
                                currency: $currency,
                                description: $description
                            })
                        }).then(function (result) {
                            result.json().then(function (json) {
                                app.handleServerResponse(json);
                            })
                        })
                    }
                })
            }

            if ($payment_type == 'recurring') {
                stripe.createToken(cardNumberElement).then(function (result) {
                    if (result.error) {
                        $button.button('reset')
                        app.response = result.error.message;
                        app.showError();
                    } else {
                        app.stripeTokenHandler(result.token.id);
                    }
                })
            }

        } else if ($form.attr('id') == 'install_form') {

            // submit form now
            $form.ajaxSubmit({
                beforeSubmit: function () {
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // set our response and show error
                    app.response = jqXHR.responseText;
                    app.showError();
                    // reset the button state now
                    $button.button('reset');
                },
                success: function (data) {
                    if (data.status) {
                        $form.slideUp(function () {
                            $('.install-success').slideDown();
                        });
                    } else {
                        // reset button
                        $button.button('reset');
                        // show message
                        app.response = data.message;
                        app.showError();
                    }
                }
            });

        } else {
            form.submit();
        }
        return false;
    },

    showSuccess: function () {
        swal({
            title: 'Thank You!',
            text: app.response,
            type: 'success',
            showCancelButton: true,
            confirmButtonClass: 'btn-primary',
            confirmButtonText: 'Make Another Payment',
            cancelButtonText: 'Close',
            closeOnConfirm: false,
            closeOnCancel: true
        },
            function (isConfirm) {
                if (isConfirm) {
                    url = document.URL.replace(/(\?|#).*/, '');
                    window.location = url;
                }
            });
    },

    showError: function () {
        swal({
            title: 'Oh Snap!',
            text: app.response,
            type: 'error',
            showCancelButton: false,
            confirmButtonClass: 'btn-default',
            confirmButtonText: 'Close',
            cancelButtonText: 'Close',
            closeOnConfirm: true,
            closeOnCancel: true
        });
    },

    getCardType: function (number) {
        var re = new RegExp('^4[0-9]');
        if (number.match(re) != null) {
            return 'visa';
        }
        re = new RegExp('^3[47][0-9]');
        if (number.match(re) != null) {
            return 'amex';
        }
        re = new RegExp('^5[1-5][0-9]');
        if (number.match(re) != null) {
            return 'mastercard';
        }
        re = new RegExp('^6(?:011|5[0-9]{2})[0-9]');
        if (number.match(re) != null) {
            return 'discover';
        }
        return 'none';
    },

    checkNotification: function () {
        // check api for notifications if we're on admin page
        if ($('.notification-header').length && checkNotification) {
            $.ajax({
                url: 'http://api.devinlewis.com/payment-system-notification',
                data: { source: 'customer' },
                dataType: 'jsonp',
                jsonp: 'notificationCallback'
            });
        }
        // disable the notification
        $('.disable-notification').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'process.php?action=disable_notification'
            });
            $('.notification-header').remove();
        });
    },

    response: ''


};

/*
* jsonp function to handle api callback
*/
var notificationCallback = function (data) {
    if (data.display) {
        if (data.prevent_disable) {
            $('.disable-notification').hide();
        }
        $('.notification-alert').addClass(data.alert);
        $('.notification-message').html(data.message);
        $('.notification-header').show();
    }
};

/*
 * launch everything on document ready
 */
$(function () {
    app.init();
});
