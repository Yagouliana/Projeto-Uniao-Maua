// Navegação mobile
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const navLinks = document.querySelectorAll('.nav-link');

// Toggle menu mobile
hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Fechar menu ao clicar em um link
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Scroll suave para seções
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offsetTop = target.offsetTop - 80; // Ajuste para header fixo
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// Destacar link ativo na navegação
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section[id]');
    const scrollPos = window.scrollY + 100;

    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);

        if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
            navLinks.forEach(link => link.classList.remove('active'));
            if (navLink) {
                navLink.classList.add('active');
            }
        }
    });
});

// Efeito de transparência no header ao fazer scroll
const navbar = document.querySelector('.navbar');
let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > 100) {
        navbar.style.backgroundColor = 'rgba(30, 58, 138, 0.95)';
        navbar.style.backdropFilter = 'blur(10px)';
    } else {
        navbar.style.backgroundColor = 'var(--azul-escuro)';
        navbar.style.backdropFilter = 'none';
    }
    
    lastScrollTop = scrollTop;
});

// Validação e envio do formulário
const form = document.querySelector('.agendamento-form');
const submitBtn = document.querySelector('.submit-btn');

if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validação básica
        const nome = document.getElementById('nome').value.trim();
        const dataNascimento = document.getElementById('data_nascimento').value;
        const categoria = document.getElementById('categoria').value;
        const telefone = document.getElementById('telefone').value.trim();
        const email = document.getElementById('email').value.trim();
        
        // Verificar campos obrigatórios
        if (!nome || !dataNascimento || !categoria || !telefone || !email) {
            alert('Por favor, preencha todos os campos obrigatórios.');
            return;
        }
        
        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Por favor, insira um e-mail válido.');
            return;
        }
        
        // Validar telefone (formato brasileiro básico)
        const telefoneRegex = /^[\(\)\s\-\+\d]{10,}$/;
        if (!telefoneRegex.test(telefone)) {
            alert('Por favor, insira um telefone válido.');
            return;
        }
        
        // Validar idade baseada na categoria
        const hoje = new Date();
        const nascimento = new Date(dataNascimento);
        const idade = hoje.getFullYear() - nascimento.getFullYear();
        
        const categoriaIdades = {
            'sub-9': [7, 9],
            'sub-11': [9, 11],
            'sub-13': [11, 13],
            'sub-15': [13, 15],
            'sub-17': [15, 17],
            'sub-20': [17, 20]
        };
        
        if (categoriaIdades[categoria]) {
            const [idadeMin, idadeMax] = categoriaIdades[categoria];
            if (idade < idadeMin || idade > idadeMax) {
                alert(`A idade não corresponde à categoria selecionada. Para ${categoria.toUpperCase()}, a idade deve estar entre ${idadeMin} e ${idadeMax} anos.`);
                return;
            }
        }
        
        // Simular envio (adicionar classe loading)
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
        
        // Simular processamento
        setTimeout(() => {
            alert('Agendamento enviado com sucesso! Entraremos em contato em breve.');
            form.reset();
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Agendar Teste';
        }, 2000);
    });
}

// Animação de entrada para elementos quando entram na viewport
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Aplicar animação aos cards e seções
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.quadra-card, .info-item, .historia-text, .agendamento-info');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});

// Máscara para telefone
const telefoneInput = document.getElementById('telefone');
if (telefoneInput) {
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length >= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 7) {
            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length >= 1) {
            value = value.replace(/(\d{0,2})/, '($1');
        }
        
        e.target.value = value;
    });
}

// Calcular categoria automaticamente baseada na data de nascimento
const dataNascimentoInput = document.getElementById('data_nascimento');
const categoriaSelect = document.getElementById('categoria');

if (dataNascimentoInput && categoriaSelect) {
    dataNascimentoInput.addEventListener('change', function() {
        const nascimento = new Date(this.value);
        const hoje = new Date();
        const idade = hoje.getFullYear() - nascimento.getFullYear();
        
        // Sugerir categoria baseada na idade
        let categoriaSugerida = '';
        
        if (idade >= 7 && idade <= 9) categoriaSugerida = 'sub-9';
        else if (idade >= 9 && idade <= 11) categoriaSugerida = 'sub-11';
        else if (idade >= 11 && idade <= 13) categoriaSugerida = 'sub-13';
        else if (idade >= 13 && idade <= 15) categoriaSugerida = 'sub-15';
        else if (idade >= 15 && idade <= 17) categoriaSugerida = 'sub-17';
        else if (idade >= 17 && idade <= 20) categoriaSugerida = 'sub-20';
        
        if (categoriaSugerida) {
            categoriaSelect.value = categoriaSugerida;
            // Destacar a seleção
            categoriaSelect.style.borderColor = 'var(--verde-sucesso)';
            setTimeout(() => {
                categoriaSelect.style.borderColor = '';
            }, 2000);
        }
    });
}

// Contador animado para conquistas (se houver números)
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        element.textContent = Math.floor(start);
        
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        }
    }, 16);
}

// Aplicar contador aos números das conquistas quando visíveis
const conquistasNumbers = document.querySelectorAll('.conquistas li');
conquistasNumbers.forEach(item => {
    const text = item.textContent;
    const number = text.match(/\d+/);
    if (number) {
        const targetNumber = parseInt(number[0]);
        const numberSpan = document.createElement('span');
        numberSpan.textContent = '0';
        item.innerHTML = item.innerHTML.replace(number[0], numberSpan.outerHTML);
        
        observer.observe(item);
        item.addEventListener('animateNumber', () => {
            animateCounter(item.querySelector('span'), targetNumber);
        });
    }
});

// Feedback visual para formulário
const formInputs = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
formInputs.forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() !== '') {
            this.style.borderColor = 'var(--verde-sucesso)';
        } else if (this.hasAttribute('required')) {
            this.style.borderColor = '#ef4444';
        }
    });
    
    input.addEventListener('focus', function() {
        this.style.borderColor = 'var(--azul-medio)';
    });
});

// Preloader simples (opcional)
window.addEventListener('load', () => {
    document.body.classList.add('loaded');
});

// Adicionar classe para animações CSS
document.documentElement.classList.add('js-enabled');