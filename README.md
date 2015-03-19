# ListaFirme
Symfony 2 Bundle for Lista Firme API

## 1. Installation:
Add this to composer.json
<pre><code>
"stev/lista-firme-bundle": "0.1.*@dev"
</code></pre>

Add this in app/AppKernel.php
<pre><code>
new Stev\ListaFirmeBundle\StevListaFirmeBundle()
</code></pre>

Add this in app/config.yml
<pre><code>
stev_lista_firme:
    username: demo
    password: demo
</code></pre>

## 2. Usage
<pre><code>
/* @var $listaFirme \Stev\ListaFirmeBundle\Lib\ListaFirme */
    $listaFirme = $this->get('stev.lista_firme');
    $response = $listaFirme->checkCompanyByCUI($cui);
</code></pre>

Lista Firme API documentation can be found at http://www.verificaretva.ro/serviciul_tva_api_web_service.htm 
