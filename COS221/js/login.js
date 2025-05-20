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
    })
})