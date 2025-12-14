let menu = document.querySelector('#menu-btn');
let navigationBar = document.querySelector('.header .navbar');

menu.onclick = () =>{
    menu.classList.toggle('fa-times');
    navigationBar.classList.toggle('active');
}