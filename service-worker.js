const CACHE_NAME = 'caminho-livre';

// Apenas arquivos estáticos essenciais (usando caminhos relativos para funcionar em subpastas locais)
const urlsToCache = [
    './',
    'index.html',
    'acessorios.html',
    'calcados.html',
    'carrinho.html',
    'sobre.html',
    'style.css',
    'manifest.json'
];

// 1. Instalação: baixa os arquivos estáticos de forma resiliente
self.addEventListener('install', event => {
    self.skipWaiting(); // Ativa imediatamente sem esperar fechar abas
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Tenta adicionar individualmente para que um arquivo ausente não trave todos os outros
            return Promise.allSettled(
                urlsToCache.map(url =>
                    cache.add(url).catch(err => console.warn(`Falha ao cachear ${url}:`, err))
                )
            );
        })
    );
});

// 2. Ativação: remove versões antigas do cache
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('Removendo cache antigo:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Interceptação de requisições
self.addEventListener('fetch', event => {
    const req = event.request;
    const url = new URL(req.url);

    // REGRA 1: NUNCA interceptar requisições POST/PUT/DELETE ou chamadas de API/PHP
    // Scripts PHP precisam sempre de resposta em tempo real do banco de dados!
    if (req.method !== 'GET' || url.pathname.endsWith('.php') || url.hostname.includes('viacep.com.br')) {
        return; // Deixa ir direto para a rede
    }

    // REGRA 2: Páginas HTML -> Network First (busca na rede para ver mudanças; se cair a internet, usa cache)
    if (req.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(req)
                .then(networkResponse => {
                    // Atualiza o cache com a versão mais recente recebida da rede
                    const resClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(req, resClone));
                    return networkResponse;
                })
                .catch(() => caches.match(req)) // Se estiver offline, entrega o que está no cache
        );
        return;
    }

    // REGRA 3: Arquivos estáticos (CSS, imagens, ícones) -> Cache First
    event.respondWith(
        caches.match(req).then(cachedResponse => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(req).then(networkResponse => {
                // Guarda novos recursos estáticos baixados
                if (networkResponse && networkResponse.status === 200) {
                    const resClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(req, resClone));
                }
                return networkResponse;
            });
        })
    );
});