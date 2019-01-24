if(document.getElementById('board-container') != undefined){

    var clientWidth = document.documentElement.clientWidth;

    if(clientWidth <= 700){
        document.getElementById('board-container').classList.add('board-min-sidebar');
    }

    // Обработка нажатия на гамбургер
    document.getElementById('board-humburger').onclick = function(){
        document.getElementById('board-container').classList.toggle('board-min-sidebar');
        if(clientWidth <= 500){
            if(!document.getElementById('board-container').classList.contains('board-min-sidebar')){
                document.getElementsByClassName('board-content')[0].style.display = 'none';
            }else{
                document.getElementsByClassName('board-content')[0].style.display = 'block';
            }
        }
    };

    // Обработка нажатия на свой мейл в верхнем сайдбаре
    document.getElementsByClassName('board--top-sidebar--account')[0].onclick = function(){
        var accountMoreBlock = document.getElementsByClassName('board--top-sidebar--account-more')[0];
        var accountMoreAngle = document.getElementById('board--top-sidebar-angle');
        if(accountMoreBlock.hasAttribute('hidden')){
            accountMoreBlock.removeAttribute('hidden', '');
            accountMoreAngle.classList.remove('fa-angle-down');
            accountMoreAngle.classList.add('fa-angle-up');
        }else{
            accountMoreBlock.setAttribute('hidden', '');
            accountMoreAngle.classList.remove('fa-angle-up');
            accountMoreAngle.classList.add('fa-angle-down');
        }
    };

}
