document.addEventListener('DOMContentLoaded', () => {
    const cepInput = document.getElementById('cep');
    if (!cepInput) return;

    cepInput.addEventListener('blur', function () {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(data => {
                    if (!data.erro) {
                        const logradouro = document.getElementById('logradouro');
                        const bairro = document.getElementById('bairro');
                        const cidade = document.getElementById('cidade');
                        const estado = document.getElementById('estado');

                        if (logradouro) logradouro.value = data.logradouro || '';
                        if (bairro) bairro.value = data.bairro || '';
                        if (cidade) cidade.value = data.localidade || '';
                        if (estado) estado.value = data.uf || '';
                    } else {
                        alert('CEP não encontrado.');
                    }
                })
                .catch(() => alert('Erro ao consultar o serviço de CEP.'));
        }
    });
});