//signup.js 

window.onload = function() {
    var nameElement = document.getElementById("name");
    var surnameElement = document.getElementById("surname");
    var email = document.getElementById("email");
    var password = document.getElementById("password");
    var type = document.getElementById("type");
    var form = document.getElementById("form");
    var errorEl = document.getElementById("error");
    var successEl = document.getElementById("success");

//for errors
form.addEventListener('submit', function(e) {
    e.preventDefault();
    var errorMessages = [];

    //validating name
    if (nameElement.value == "" || nameElement.value == null) {
        errorMessages.push("Name is required");
    }
    //regex for name
    var nameRegex = /^[A-Za-z-]+$/;
    if (!nameElement.value.match(nameRegex)) {
        errorMessages.push("Name must contain letters only");
    }

    //validating surname
    if (surnameElement.value == "" || surnameElement.value == null) {
        errorMessages.push("Surname is required");
    }
    //regex for surname
    var surnameRegex = /^[A-Za-z-]+$/;
    if (!surnameElement.value.match(surnameRegex)) {
        errorMessages.push("Surname must contain letters only");
    }

    //validating email
    if (email.value == "" || email.value == null) {
        errorMessages.push("Please enter your email");
    }
    //regex for email
    var emailRegex = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])\])/
    if (!email.value.match(emailRegex)) {
        errorMessages.push("Invalid email address");
    }
    //^((?:[A-Za-z0-9!#$%&'*+\-\/=?^_`{|}~]|(?<=^|\.)"|"(?=$|\.|@)|(?<=".*)[ .](?=.*")|(?<!\.)\.){1,64})(@)((?:[A-Za-z0-9.\-])*(?:[A-Za-z0-9])\.(?:[A-Za-z0-9]){2,})$


    //validating password
    //checking field is not empty
    if (password.value == "" || password.value == null) {
        errorMessages.push("Please enter your password");
    }
    //checking length of password
    if (password.value.length < 8) {
        errorMessages.push("Password must be at least 8 characters long");
    }
    //checking that it matches the regular expression
    var regex = /(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*\W).{8,}/
    if (!password.value.match(regex)) {
        errorMessages.push("Password must contain at least one uppercase letter, one lowercase letter, one digit and one special character");
    }


    if (type.value == "" || type.value == null) {
        errorMessages.push("Please select your type");
    }

    //Clear previous messages
    errorEl.innerText = "";
    successEl.innerText = "";
    //printing error messages
    if (errorMessages.length === 0) {
        //ajax to submit
        //create json object
        var JSONObject = {
            "type": "Register",
            "name": nameElement.value,
            "surname": surnameElement.value,
            "email": email.value,
            "password": password.value,
            "user_type": type.value
        };

        //xml request
        var request = new XMLHttpRequest();
        request.open("POST", "../../api.php", true); //API endpoint
        request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

        request.onreadystatechange = function() {
            if (this.readyState == 4) {
                var response = JSON.parse(this.responseText);
                console.log(response);

                if (response.status === "success") {
                    //Registration successful
                    successEl.innerText = "Registration successful! Your API key is: " + response.data.apikey;
                    form.reset();
                } 
                else {
                    // Registration failed
                    errorEl.innerText = response.data || "Registration failed. Please try again.";
                }
            }
        };
        request.send(JSON.stringify(JSONObject));
    }
    else{
        errorEl.innerText = errorMessages.join(",");
    }   
});
}

