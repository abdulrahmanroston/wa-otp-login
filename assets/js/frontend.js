/**
 * WA OTP Login - Frontend Script
 * Handles AJAX requests and UI interactions
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        console.log('WA OTP JS Loaded!');
        
        const $container = $('.wa-otp-container');
        const $phoneStep = $('#wa-otp-phone-step');
        const $verifyStep = $('#wa-otp-verify-step');
        const $message = $('#wa-otp-message');
        
        const $countrySelect = $('#wa-otp-country');
        const $countryCode = $('.wa-otp-country-code');
        const $phoneInput = $('#wa-otp-phone');
        const $codeInput = $('#wa-otp-code');
        
        const $sendBtn = $('#wa-otp-send-btn');
        const $verifyBtn = $('#wa-otp-verify-btn');
        const $resendBtn = $('#wa-otp-resend-btn');
        const $backBtn = $('#wa-otp-back-btn');
        
        let currentPhone = '';
        let currentCountry = waOtpData.defaultCountry;
        let resendTimer = null;
        let resendCountdown = 60;
        
        /**
         * Update country code display
         */
        $countrySelect.on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const dialCode = selectedOption.data('code');
            currentCountry = selectedOption.val();
            $countryCode.text('+' + dialCode);
        });
        
        /**
         * Format phone input - numbers only
         */
        $phoneInput.on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(value);
        });
        
        /**
         * Format code input - 3 digits only
         */
        $codeInput.on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '').substring(0, 3);
            $(this).val(value);
        });
        
        /**
         * Send OTP
         */
        $sendBtn.on('click', function() {
            sendOtp();
        });
        
        // Allow Enter key on phone input
        $phoneInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                sendOtp();
            }
        });
        
        /**
         * Verify OTP
         */
        $verifyBtn.on('click', function() {
            verifyOtp();
        });
        
        // Allow Enter key on code input
        $codeInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                verifyOtp();
            }
        });
        
        /**
         * Resend OTP
         */
        $resendBtn.on('click', function() {
            if (resendTimer) {
                return; // Still in cooldown
            }
            sendOtp();
        });
        
        /**
         * Back to phone step
         */
        $backBtn.on('click', function() {
            $verifyStep.hide();
            $phoneStep.show();
            $codeInput.val('');
            $message.hide();
        });
        
        /**
         * Send OTP Request
         */
        function sendOtp() {
            const phone = $phoneInput.val().trim();
            
            if (!phone) {
                showMessage('Please enter your phone number', 'error');
                return;
            }
            
            if (phone.length < 8) {
                showMessage('Phone number is too short', 'error');
                return;
            }
            
            currentPhone = phone;
            
            // Disable button
            setButtonLoading($sendBtn, true);
            $message.hide();
            
            // AJAX request
            $.ajax({
                url: waOtpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wa_otp_send',
                    nonce: waOtpData.nonce,
                    phone: phone,
                    country: currentCountry
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                        
                        // Switch to verify step
                        setTimeout(function() {
                            $phoneStep.hide();
                            $verifyStep.show();
                            $codeInput.focus();
                            startResendTimer();
                        }, 1000);
                        
                    } else {
                        showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    showMessage('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    setButtonLoading($sendBtn, false);
                }
            });
        }
        
        /**
         * Verify OTP Request
         */
        function verifyOtp() {
            const code = $codeInput.val().trim();
            
            if (!code || code.length !== 3) {
                showMessage('Please enter the 3-digit code', 'error');
                return;
            }
            
            // Disable button
            setButtonLoading($verifyBtn, true);
            $message.hide();
            
            // AJAX request
            $.ajax({
                url: waOtpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wa_otp_verify',
                    nonce: waOtpData.nonce,
                    phone: currentPhone,
                    country: currentCountry,
                    otp: code
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                        
                        // Redirect after success
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1500);
                        
                    } else {
                        showMessage(response.data.message, 'error');
                        $codeInput.val('').focus();
                    }
                },
                error: function() {
                    showMessage('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    setButtonLoading($verifyBtn, false);
                }
            });
        }
        
        /**
         * Show message
         */
        function showMessage(text, type) {
            $message
                .removeClass('success error info')
                .addClass(type)
                .html(text)
                .fadeIn();
        }
        
        /**
         * Set button loading state
         */
        function setButtonLoading($button, loading) {
            if (loading) {
                $button.prop('disabled', true);
                $button.find('.wa-otp-btn-text').hide();
                $button.find('.wa-otp-btn-loading').show();
            } else {
                $button.prop('disabled', false);
                $button.find('.wa-otp-btn-text').show();
                $button.find('.wa-otp-btn-loading').hide();
            }
        }
        
        /**
         * Start resend countdown timer
         */
        function startResendTimer() {
            resendCountdown = 60;
            $resendBtn.prop('disabled', true);
            updateResendButton();
            
            resendTimer = setInterval(function() {
                resendCountdown--;
                
                if (resendCountdown <= 0) {
                    clearInterval(resendTimer);
                    resendTimer = null;
                    $resendBtn.prop('disabled', false).text('Resend Code');
                } else {
                    updateResendButton();
                }
            }, 1000);
        }
        
        /**
         * Update resend button text
         */
        function updateResendButton() {
            $resendBtn.text('Resend Code (' + resendCountdown + 's)');
        }
        
    });
    
})(jQuery);
