
let carrinho = document.querySelector(".carrinho");
document.querySelector("#cart").onclick = () => {
	carrinho.classList.toggle('active');
	login.classList.remove('active');
	menuResponsivo.classList.remove('active');
 
}	

let login = document.querySelector(".login-form");
document.querySelector("#login").onclick = () => {
	login.classList.toggle('active');
	carrinho.classList.remove('active');
	menuResponsivo.classList.remove('active');
}



//menu responsivo

let menuResponsivo = document.querySelector('.menu-site');
document.querySelector('#menu').onclick = () =>{
	menuResponsivo.classList.toggle('active');
	login.classList.remove('active');
}


window.onscroll = () =>{
	carrinho.classList.remove('active');
	login.classList.remove('active');
	menuResponsivo.classList.remove('active');
}


var swiper = new Swiper(".home-slider", {

	autoplay:{
		delay:2500,
		disableOnInteraction: false,
	},

	
	grabCursor:true,
	loop:true,
	centeredSlides:true,


	navigation: {
		nextEl: '.swiper-button-next',
		prevEl: '.swiper-button-prev',
	},

})




var swiper = new Swiper(".menu-slider", {
	
	grabCursor:true,
	loop:true,
	autoHeight:true,
	centeredSlides:true,
	spaceBetween:20,
	pagination:{
		el:'.swiper-pagination',
		clickable:true,
	},

})


/** JANELA MODAL**/

let vermodalCorpo = document.querySelector(".menu-modal-container");
if (vermodalCorpo) {
	let vermodalBox = vermodalCorpo.querySelectorAll(".menu-modal");

	document.querySelectorAll(".menu .box").forEach(menu =>{
		menu.onclick = () =>{
			vermodalCorpo.style.display = 'flex';
			let nome = menu.getAttribute('data-name');

			vermodalBox.forEach(visualizar => {
				let chamada = visualizar.getAttribute('data-target');
				if (nome == chamada) {
					visualizar.classList.add('active');
				}
			})
		}
	})

	let fecharBtn = vermodalCorpo.querySelector('#fechar');
	if (fecharBtn) {
		fecharBtn.onclick = () => {
			vermodalCorpo.style.display= 'none';
			vermodalBox.forEach(fechar => {
				fechar.classList.remove('active');
			})
		};
	}
}

// --- ROLAGEM SUAVE INTELIGENTE E COMPENSAÇÃO DE MENU FIXO ---
document.querySelectorAll('a[href^="#"], a[href*="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        let href = this.getAttribute('href');
        let hashIndex = href.indexOf('#');
        
        if (hashIndex !== -1) {
            let hash = href.substring(hashIndex);
            let target = document.querySelector(hash);
            
            if (target) {
                e.preventDefault();
                
                // Fecha o menu responsivo se estiver aberto
                if (typeof menuResponsivo !== 'undefined') {
                    menuResponsivo.classList.remove('active');
                }
                
                // Calcula altura do cabeçalho fixo para compensar a rolagem
                let header = document.querySelector('.topo-site');
                let headerHeight = header ? header.offsetHeight : 80;
                let targetPosition = target.getBoundingClientRect().top + window.pageYOffset - (headerHeight + 20); // 20px de respiro
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Atualiza a URL sem dar o pulo brusco
                history.pushState(null, null, hash);
            }
        }
    });
});

// Deslizamento suave automático caso o usuário chegue de outra página com a âncora na URL
window.addEventListener('load', () => {
    if (window.location.hash) {
        let target = document.querySelector(window.location.hash);
        if (target) {
            // Reseta temporariamente para o topo para permitir a transição
            window.scrollTo(0, 0);
            setTimeout(() => {
                let header = document.querySelector('.topo-site');
                let headerHeight = header ? header.offsetHeight : 80;
                let targetPosition = target.getBoundingClientRect().top + window.pageYOffset - (headerHeight + 20);
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }, 150);
        }
    }
});

// --- TOGGLE LOGIN POPUP DESDE O LINK DA PÁGINA DE CADASTRO ---
document.addEventListener('DOMContentLoaded', () => {
    let openLoginBtn = document.querySelector("#open-login-modal");
    let loginFormDropdown = document.querySelector(".login-form");
    if (openLoginBtn && loginFormDropdown) {
        openLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginFormDropdown.classList.add('active');
            if (typeof carrinho !== 'undefined') carrinho.classList.remove('active');
            if (typeof menuResponsivo !== 'undefined') menuResponsivo.classList.remove('active');
            
            // Rolar suavemente de volta ao topo onde o cabeçalho/dropdown de login está fixado
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});