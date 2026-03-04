<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name} {--namespace=}';

    protected $description = 'Cria interface e classe concreta de um Repository';

    private array $namespaces = [
        'interface' => 'App\\Contracts',
        'concreta' => 'App\\Repositories',
    ];

    private string $nome;

    private ?string $namespace;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->nome = $this->argument('name');
        $this->namespace = $this->option('namespace');
        $interface = $this->criaInterface();
        $implementacao = $this->criaImplementacao();
        $this->registraBinding($interface, $implementacao);
        $this->info('Repository criado com sucesso!');

        return self::SUCCESS;
    }

    private function criaInterface(): object
    {
        $nomeCompleto = is_null($this->namespace) ? $this->nome : "{$this->namespace}/{$this->nome}";
        $pathInterface = app_path("Contracts/{$nomeCompleto}.php");
        $namespace = is_null($this->namespace) ? '' : "\\{$this->namespace}";
        $this->geraPorStub(base_path('stubs/repository/interface.stub'), $pathInterface, [
            '{{ namespace }}' => $this->namespaces['interface'] . $namespace,
            '{{ interface }}' => $this->nome,
        ]);
        $this->comment("{$nomeCompleto} criado");

        return (object) ['nome' => $this->nome, 'namespace' => $this->namespaces['interface'] . $namespace];
    }

    private function criaImplementacao(): object
    {
        $nomeConcreto = "Eloquent{$this->nome}";
        $nomeCompleto = is_null($this->namespace) ? $nomeConcreto : "{$this->namespace}/{$nomeConcreto}";
        $pathImplementacao = app_path("Repositories/{$nomeCompleto}.php");
        $namespace = is_null($this->namespace) ? '' : "\\{$this->namespace}";
        $this->geraPorStub(base_path('stubs/repository/implementation.stub'), $pathImplementacao, [
            '{{ namespace }}' => $this->namespaces['concreta'] . $namespace,
            '{{ class }}' => "Eloquent{$this->nome}",
            '{{ interfaceNamespace }}' => $this->namespaces['interface'] . $namespace,
            '{{ interface }}' => $this->nome,
        ]);
        $this->comment("{$nomeCompleto} criado");

        return (object) ['nome' => $nomeConcreto, 'namespace' => $this->namespaces['concreta'] . $namespace];
    }

    private function registraBinding(object $interface, object $implementacao): void
    {
        $provider = app_path('Providers/RepositoryServiceProvider.php');
        if (! File::exists($provider)) {
            $this->error('RepositoryServiceProvider não encontrado');

            return;
        }
        $binding = "\$this->app->bind(\\{$interface->namespace}\\{$interface->nome}::class, \\{$implementacao->namespace}\\{$implementacao->nome}::class);";
        $conteudoProvider = File::get($provider);
        if (str_contains($conteudoProvider, $binding)) {
            $this->warn('Repository já registrado no RepositoryServiceProvider');

            return;
        }
        $conteudo = preg_replace(
            '/public function register\(\): void\s*\n\s*\{/',
            "public function register(): void\n    {\n        {$binding}",
            $conteudoProvider
        );
        File::put($provider, $conteudo);
        $this->comment('Binding registado no RepositoryServiceProvider');
    }

    private function geraPorStub(string $pathStub, string $destino, array $substituicoes): void
    {
        if (File::exists($destino)) {
            $this->warn("Arquivo já existe em {$destino}");

            return;
        }
        $stub = File::get($pathStub);
        foreach ($substituicoes as $key => $substituicao) {
            $stub = str_replace($key, $substituicao, $stub);
        }
        File::ensureDirectoryExists(dirname($destino));
        File::put($destino, $stub);
    }
}
