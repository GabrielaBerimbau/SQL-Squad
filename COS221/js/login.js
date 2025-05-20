document.addEventListener('DOMContentLoaded', function(){
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-msg');

    loginForm.addEventListener('submit', function(e){
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        if(!username || !password){
            showError('Please enter a username and password');
            return;
        }

        const formData = new FormData();

        formData.append('username', username);
        formData.append('password', password);

        fetch('../authenticate.php',{
            method: 'POST', body: formData
        }) .then(response=> {
            if(!response.ok){
                throw new Error('Error with network response');
            }

            return response.json();
        }) .then(data=> {
            if(data.success){

                window.location.href = data.redirect;
            }

            else{
                showError(data.message || 'Invalid username or password');
            }
        })
        .catch(error=> {
            console.error('Error: ', error);
            showError('An error occured during login. Please try again later.')
        });
    });

    function showError(message){
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';

        // hiding the error msg after 5 secs
        setTimeout(()=> {
            errorMessage.style.display = 'none';
        }, 5000);
    }
});