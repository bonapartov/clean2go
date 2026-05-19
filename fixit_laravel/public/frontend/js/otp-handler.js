/**
 * OTP Handling Script (independent)
 * Safely manages input focus & merges OTP into hidden field
 */
document.addEventListener("DOMContentLoaded", function () {
    const otpInputs = document.querySelectorAll(".otp__digit");
    const otpHidden = document.getElementById("otp_hidden");
    const otpForm = document.getElementById("otpForm");

    if (!otpInputs.length || !otpForm || !otpHidden) return;

    otpInputs.forEach((input, index) => {
        input.addEventListener("input", function (e) {
            // Allow only single numeric value
            this.value = this.value.replace(/[^0-9]/g, "").slice(0, 1);

            // Move to next input automatically
            if (this.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            // Update hidden OTP value every time a digit changes
            updateHiddenOtp();
        });

        input.addEventListener("keydown", function (e) {
            // Handle backspace to move to previous input
            if (e.key === "Backspace" && !this.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    // Before form submit, ensure all digits are combined
    otpForm.addEventListener("submit", function () {
        updateHiddenOtp();
    });

    function updateHiddenOtp() {
        let otpValue = "";
        otpInputs.forEach((i) => (otpValue += i.value));
        otpHidden.value = otpValue;
    }
});