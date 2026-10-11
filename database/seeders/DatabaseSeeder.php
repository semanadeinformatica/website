<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Competition;
use App\Models\CompetitionPrize;
use App\Models\CompetitionTeam;
use App\Models\Department;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Participant;
use App\Models\Product;
use App\Models\Quest;
use App\Models\Slot;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorTier;
use App\Models\Staff;
use App\Models\Stand;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    const DEFAULT_ADMIN_EMAIL = 'admin@example.com';

    private function cleanDatabase(): void
    {
        $tables = [
            'competition_team_participant',
            'competition_prizes',
            'competition_teams',
            'competitions',
            'enrollment_event',
            'enrollment_quest',
            'enrollment_product',
            'enrollment_slot',
            'enrollments',
            'event_user',
            'events',
            'event_days',
            'event_types',
            'stands',
            'sponsors',
            'sponsor_tiers',
            'quests',
            'quest_slot',
            'slots',
            'products',
            'staff',
            'departments',
            'speakers',
            'companies',
            'participants',
            'admins',
            'social_media',
            'editions',
            'users',
        ];

        DB::statement('TRUNCATE TABLE '.implode(', ', $tables).' CASCADE');
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Clearing the database');
        $this->cleanDatabase();

        $this->command->info('Creating the admin user');
        if (! User::where('email', '=', static::DEFAULT_ADMIN_EMAIL)->exists()) {
            User::factory()->admin()->create([
                'name' => 'Admin SINF',
                'email' => static::DEFAULT_ADMIN_EMAIL,
            ]);
        }

        $this->command->info('Creating participants');
        $participants = Participant::factory(60)->create();
        $participant_users = $participants->pluck('user');

        $this->command->info('Creating companies');
        $companyNames = [
            'Critical TechWorks',
            'Feedzai',
            'Talkdesk',
            'Blip',
            'Farfetch',
            'Sword Health',
            'Natixis in Portugal',
            'Deloitte',
            'Accenture',
            'Microsoft Portugal',
            'Google Cloud',
            'Bosch Portugal',
            'Siemens',
            'Continental Engineering Services',
            'OutSystems',
            'Mindera',
            'Cisco Systems',
            'Revolut',
        ];

        $companies = collect($companyNames)->map(function ($name) {
            $company = Company::factory()->create();
            $slug = Str::slug($name);
            $company->user->update([
                'name' => $name,
                'email' => "careers@{$slug}.com",
            ]);

            return $company;
        });
        $company_users = $companies->pluck('user');

        $this->command->info('Creating speakers');
        $speakerData = [
            ['name' => 'Prof. Ademar Aguiar', 'organization' => 'FEUP', 'title' => 'Professor Associado', 'description' => 'Especialista em Engenharia de Software, Design Patterns e Metodologias Ágeis.'],
            ['name' => 'Inês Santos Silva', 'organization' => 'Aliados Consulting', 'title' => 'Tech Lead & Founder', 'description' => 'Focada em liderança de engenharia, cultura de produto e impacto digital.'],
            ['name' => 'Rui Carmo', 'organization' => 'Cloudflare', 'title' => 'Principal Systems Engineer', 'description' => 'Engenheiro de infraestruturas cloud distribuídas e sistemas de edge computing.'],
            ['name' => 'Marta Ferreira', 'organization' => 'Feedzai', 'title' => 'Head of AI Research', 'description' => 'Lidera equipas de investigação em inteligência artificial e deteção de anomalias.'],
            ['name' => 'Diogo Silva', 'organization' => 'Talkdesk', 'title' => 'VP of Engineering', 'description' => 'Especialista em arquitetura de microsserviços em larga escala e VoIP.'],
            ['name' => 'Sara Mendes', 'organization' => 'Critical TechWorks', 'title' => 'Senior DevOps Engineer', 'description' => 'Entusiasta de GitOps, Kubernetes e automação contínua de plataformas.'],
            ['name' => 'Gonçalo Pestana', 'organization' => 'Web3 Foundation', 'title' => 'Core Protocol Engineer', 'description' => 'Investigador em protocolos descentralizados, criptografia e sistemas P2P.'],
            ['name' => 'Beatriz Carvalho', 'organization' => 'Sword Health', 'title' => 'Senior Product Designer', 'description' => 'Focada em experiência de utilizador e acessibilidade em dispositivos de saúde.'],
            ['name' => 'Tiago Morais', 'organization' => 'Checkmarx', 'title' => 'AppSec Specialist', 'description' => 'Investigador de vulnerabilidades de segurança ofensiva e análise de código.'],
            ['name' => 'Ana Rita Lourenço', 'organization' => 'Mindera', 'title' => 'Staff Software Engineer', 'description' => 'Arquiteta de sistemas distribuídos e entusiasta de linguagens funcionais como Rust e Elixir.'],
        ];

        $speakers = collect($speakerData)->map(function ($data) {
            $speaker = Speaker::factory()->create([
                'organization' => $data['organization'],
                'title' => $data['title'],
                'description' => $data['description'],
            ]);
            $slug = Str::slug($data['name']);
            $speaker->user->update([
                'name' => $data['name'],
                'email' => "{$slug}@sinf.pt",
            ]);

            return $speaker;
        });
        $speaker_users = $speakers->pluck('user');

        $this->command->info('Creating the edition');
        $editionYear = intval(env('EDITION', Carbon::now()->year));
        $edition = Edition::firstOrCreate(
            ['year' => $editionYear],
            ['name' => "SINF {$editionYear}"]
        );

        $this->command->info('Creating event days');
        $daysConfig = [
            [
                'date' => Carbon::create($editionYear, 11, 16)->toDateString(),
                'theme' => 'Inteligência Artificial & Machine Learning',
            ],
            [
                'date' => Carbon::create($editionYear, 11, 17)->toDateString(),
                'theme' => 'Cibersegurança & Infraestrutura',
            ],
            [
                'date' => Carbon::create($editionYear, 11, 18)->toDateString(),
                'theme' => 'Engenharia de Software & Cloud Native',
            ],
            [
                'date' => Carbon::create($editionYear, 11, 19)->toDateString(),
                'theme' => 'Inovação, Web3 & O Futuro da Tecnologia',
            ],
        ];

        $event_days = collect($daysConfig)->map(function ($config) use ($edition) {
            return EventDay::create([
                'edition_id' => $edition->id,
                'date' => $config['date'],
                'theme' => $config['theme'],
            ]);
        });

        $this->command->info('Creating event types');
        $talkType = EventType::firstOrCreate(['name' => 'talk']);
        $workshopType = EventType::firstOrCreate(['name' => 'workshop']);

        $this->command->info('Creating events');
        $eventsSchedule = [
            // Day 1
            [
                'day_index' => 0,
                'name' => 'Abertura Oficial & O Futuro da IA Generativa',
                'topic' => 'Inteligência Artificial',
                'time_start' => '10:00:00',
                'time_end' => '11:15:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Sessão de abertura da SINF seguida de palestra sobre as tendências mais recentes no panorama da inteligência artificial generativa e os seus impactos na indústria.',
            ],
            [
                'day_index' => 0,
                'name' => 'LLMs em Produção: Desafios de Latência, Custo e Fiabilidade',
                'topic' => 'Machine Learning',
                'time_start' => '11:30:00',
                'time_end' => '12:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Uma discussão aprofundada sobre como levar modelos fundacionais de Machine Learning do protótipo até sistemas reais em produção de larga escala.',
            ],
            [
                'day_index' => 0,
                'name' => 'Workshop: Agentes Autónomos com Python e LangChain',
                'topic' => 'Inteligência Artificial Prática',
                'time_start' => '14:30:00',
                'time_end' => '16:00:00',
                'type' => $workshopType,
                'location' => 'Sala I121',
                'capacity' => 30,
                'enroll_in_site' => true,
                'description' => 'Neste workshop prático, os participantes irão construir agentes autónomos com ferramentas, memória e encadeamento de prompts complexos.',
            ],
            [
                'day_index' => 0,
                'name' => 'Ética, Governança e Alinhamento em Modelos Fundacionais',
                'topic' => 'Ética & Sociedade',
                'time_start' => '16:30:00',
                'time_end' => '17:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Exploração dos dilemas éticos, riscos de alinhamento e regulação europeia sobre a criação e distribuição de modelos de IA.',
            ],
            [
                'day_index' => 0,
                'name' => 'Workshop: Fine-Tuning de Modelos Open-Source',
                'topic' => 'Deep Learning',
                'time_start' => '18:00:00',
                'time_end' => '19:30:00',
                'type' => $workshopType,
                'location' => 'Sala I122',
                'capacity' => 25,
                'enroll_in_site' => true,
                'description' => 'Aprende a aplicar técnicas de LoRA e QLoRA para adaptar modelos open-source aos teus próprios conjuntos de dados.',
            ],
            // Day 2
            [
                'day_index' => 1,
                'name' => 'Anatomia de um Ciberataque: Lições da Linha da Frente',
                'topic' => 'Cibersegurança',
                'time_start' => '10:00:00',
                'time_end' => '11:15:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Uma análise detalhada de incidentes reais de segurança, vetores de ataque comuns e estratégias de resposta rápida.',
            ],
            [
                'day_index' => 1,
                'name' => 'Zero Trust Architecture na Prática Corporativa',
                'topic' => 'Infraestrutura Segura',
                'time_start' => '11:30:00',
                'time_end' => '12:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Como implementar autenticação contínua, segmentação de rede e controlo de acessos baseado no princípio de privilégio mínimo.',
            ],
            [
                'day_index' => 1,
                'name' => 'Workshop: Introdução a Exploração de Binários e Engenharia Reversa',
                'topic' => 'Segurança Ofensiva',
                'time_start' => '14:30:00',
                'time_end' => '16:00:00',
                'type' => $workshopType,
                'location' => 'Sala I121',
                'capacity' => 30,
                'enroll_in_site' => true,
                'description' => 'Desmontagem de executáveis, identificação de buffer overflows e análise de vulnerabilidades com ferramentas como Ghidra e GDB.',
            ],
            [
                'day_index' => 1,
                'name' => 'Segurança na Supply Chain de Software Open Source',
                'topic' => 'AppSec & DevSecOps',
                'time_start' => '16:30:00',
                'time_end' => '17:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Como mitigar riscos de injeção de dependências maliciosas, assinar artefactos de software e gerir SBOMs eficientemente.',
            ],
            [
                'day_index' => 1,
                'name' => 'Workshop: Hardening de Clusters Kubernetes em Produção',
                'topic' => 'Cloud Security',
                'time_start' => '18:00:00',
                'time_end' => '19:30:00',
                'type' => $workshopType,
                'location' => 'Sala I122',
                'capacity' => 25,
                'enroll_in_site' => true,
                'description' => 'Políticas de segurança, NetworkPolicies, RBAC restritivo e auditoria de pods num cluster Kubernetes real.',
            ],
            // Day 3
            [
                'day_index' => 2,
                'name' => 'Do Monólito a Microsserviços Event-Driven: Lições e Arrependimentos',
                'topic' => 'Arquitetura de Software',
                'time_start' => '10:00:00',
                'time_end' => '11:15:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Reflexão sobre os prós e contras da decomposição de sistemas, consistência eventual e gestão da complexidade operacional.',
            ],
            [
                'day_index' => 2,
                'name' => 'Observabilidade Moderna com OpenTelemetry e Grafana',
                'topic' => 'DevOps & SRE',
                'time_start' => '11:30:00',
                'time_end' => '12:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Passagem de logs isolados para rastreamento distribuído, métricas contextuais e diagnósticos preditivos em sistemas complexos.',
            ],
            [
                'day_index' => 2,
                'name' => 'Workshop: APIs Reativas e Concorrentes com Rust e Axum',
                'topic' => 'Desenvolvimento em Rust',
                'time_start' => '14:30:00',
                'time_end' => '16:00:00',
                'type' => $workshopType,
                'location' => 'Sala I121',
                'capacity' => 30,
                'enroll_in_site' => true,
                'description' => 'Construção de uma API HTTP de alta performance com tipagem segura, gestão assíncrona com Tokio e manipulação de estado em memória.',
            ],
            [
                'day_index' => 2,
                'name' => 'Streaming de Dados em Larga Escala com Apache Kafka',
                'topic' => 'Data Engineering',
                'time_start' => '16:30:00',
                'time_end' => '17:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Padrões de processamento em tempo real, particionamento de tópicos e garantia de entrega Exactly-Once.',
            ],
            [
                'day_index' => 2,
                'name' => 'Workshop: Testes de Carga Automatizados com k6',
                'topic' => 'Performance & QA',
                'time_start' => '18:00:00',
                'time_end' => '19:30:00',
                'type' => $workshopType,
                'location' => 'Sala I122',
                'capacity' => 25,
                'enroll_in_site' => true,
                'description' => 'Simulação de milhares de utilizadores concorrentes, identificação de estrangulamentos e integração em pipelines de CI/CD.',
            ],
            // Day 4
            [
                'day_index' => 3,
                'name' => 'Computação Quântica: O Que Já Funciona e O Que Ainda é Ficção',
                'topic' => 'Tecnologias Emergentes',
                'time_start' => '10:00:00',
                'time_end' => '11:15:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Uma introdução acessível aos qubits, portas quânticas e os algoritmos que prometem revolucionar a criptografia e otimização.',
            ],
            [
                'day_index' => 3,
                'name' => 'Smart Contracts & Protocolos de Larga Escala',
                'topic' => 'Web3 & Criptografia',
                'time_start' => '11:30:00',
                'time_end' => '12:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Desafios de escalabilidade com Rollups, verificação formal de contratos inteligentes e soluções de interoperabilidade entre redes.',
            ],
            [
                'day_index' => 3,
                'name' => 'Workshop: Construir a tua Primeira dApp com Solidity e Viem',
                'topic' => 'Web3 Hands-on',
                'time_start' => '14:30:00',
                'time_end' => '16:00:00',
                'type' => $workshopType,
                'location' => 'Sala I121',
                'capacity' => 30,
                'enroll_in_site' => true,
                'description' => 'Desenvolvimento de um contrato inteligente, testes automatizados e integração com uma interface web moderna.',
            ],
            [
                'day_index' => 3,
                'name' => 'Mesa Redonda: O Engenheiro de Software em 2030',
                'topic' => 'Carreira & Futuro',
                'time_start' => '16:30:00',
                'time_end' => '17:45:00',
                'type' => $talkType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Líderes de engenharia e docentes debatem a evolução do papel dos programadores com a assistência de IA e o mercado global de trabalho.',
            ],
            [
                'day_index' => 3,
                'name' => 'Sessão de Encerramento e Entrega de Prémios',
                'topic' => 'Encerramento',
                'time_start' => '18:00:00',
                'time_end' => '19:30:00',
                'type' => $workshopType,
                'location' => 'Auditório B001',
                'capacity' => null,
                'enroll_in_site' => false,
                'description' => 'Celebração de encerramento da SINF 2026, anúncio dos vencedores do Hackathon, CTF e Game Jam, e sorteio de prémios finais.',
            ],
        ];

        $events = collect($eventsSchedule)->map(function ($ev) use ($event_days, $speaker_users, $company_users) {
            $eventDay = $event_days[$ev['day_index']];
            $event = Event::create([
                'event_day_id' => $eventDay->id,
                'event_type_id' => $ev['type']->id,
                'name' => $ev['name'],
                'topic' => $ev['topic'],
                'time_start' => $ev['time_start'],
                'time_end' => $ev['time_end'],
                'location' => $ev['location'],
                'capacity' => $ev['capacity'],
                'enroll_in_site' => $ev['enroll_in_site'],
                'description' => $ev['description'],
                'external_url' => null,
            ]);

            // Attach 1-2 speakers
            $speakersToAttach = $speaker_users->random(rand(1, 2));
            $event->users()->attach($speakersToAttach);

            // Attach 1-2 sponsor companies
            $companiesToAttach = $company_users->random(rand(1, 2));
            $event->users()->attach($companiesToAttach);

            return $event;
        });

        $this->command->info('Creating departments and staff');
        $departmentsData = [
            ['name' => 'Coordenação Geral', 'priority' => 10],
            ['name' => 'Relações Externas e Patrocínios', 'priority' => 8],
            ['name' => 'Programa e Conteúdos', 'priority' => 8],
            ['name' => 'Logística e Operações', 'priority' => 7],
            ['name' => 'Design e Multimédia', 'priority' => 6],
            ['name' => 'Informática e Sistemas', 'priority' => 6],
        ];

        $departments = collect($departmentsData)->map(function ($dep) use ($edition) {
            return Department::create([
                'edition_id' => $edition->id,
                'name' => $dep['name'],
                'priority' => $dep['priority'],
            ]);
        });

        // Assign staff to departments
        $participantPool = $participants->shuffle();
        $departments->each(function ($department, $index) use ($participantPool) {
            $deptParticipants = $participantPool->slice($index * 3, 3);
            $deptParticipants->each(function ($participant, $pIndex) use ($department) {
                Staff::create([
                    'department_id' => $department->id,
                    'participant_id' => $participant->id,
                    'coordinator' => $pIndex === 0,
                ]);
            });
        });

        $this->command->info('Creating sponsor tiers, sponsors and stands');
        $platinumTier = SponsorTier::create([
            'name' => 'Platinum',
            'color' => '#E5E4E2',
            'rank' => 3,
            'canSeeCV' => true,
            'canSeeLinkedin' => true,
            'canSeeAll' => true,
            'edition_id' => $edition->id,
        ]);

        $goldTier = SponsorTier::create([
            'name' => 'Gold',
            'color' => '#FFD700',
            'rank' => 2,
            'canSeeCV' => true,
            'canSeeLinkedin' => true,
            'canSeeAll' => false,
            'edition_id' => $edition->id,
        ]);

        $silverTier = SponsorTier::create([
            'name' => 'Silver',
            'color' => '#C0C0C0',
            'rank' => 1,
            'canSeeCV' => false,
            'canSeeLinkedin' => true,
            'canSeeAll' => false,
            'edition_id' => $edition->id,
        ]);

        // 3 Platinum, 7 Gold, 8 Silver = 18 sponsors
        $sponsors = collect();
        $companies->each(function ($company, $index) use ($edition, $platinumTier, $goldTier, $silverTier, &$sponsors) {
            if ($index < 3) {
                $tier = $platinumTier;
            } elseif ($index < 10) {
                $tier = $goldTier;
            } else {
                $tier = $silverTier;
            }

            $sponsor = Sponsor::create([
                'edition_id' => $edition->id,
                'company_id' => $company->id,
                'sponsor_tier_id' => $tier->id,
            ]);
            $sponsors->push($sponsor);
        });

        // Create stands for sponsors across event days
        $stands = collect();
        $sponsors->each(function ($sponsor) use ($event_days, $platinumTier, $goldTier, &$stands) {
            if ($sponsor->sponsor_tier_id === $platinumTier->id) {
                // Platinum on all 4 days
                $days = $event_days;
            } elseif ($sponsor->sponsor_tier_id === $goldTier->id) {
                // Gold on 2-3 days
                $days = $event_days->random(rand(2, 3));
            } else {
                // Silver on 1-2 days
                $days = $event_days->random(rand(1, 2));
            }

            foreach ($days as $day) {
                $stand = Stand::create([
                    'event_day_id' => $day->id,
                    'sponsor_id' => $sponsor->id,
                ]);
                $stands->push($stand);
            }
        });

        $this->command->info('Creating products');
        $productsData = [
            ['name' => 'T-Shirt Oficial SINF 2026', 'price' => 15, 'stock' => 100],
            ['name' => 'Sweatshirt SINF', 'price' => 25, 'stock' => 60],
            ['name' => 'Tote Bag SINF', 'price' => 8, 'stock' => 120],
            ['name' => 'Caneca Térmica SINF', 'price' => 12, 'stock' => 50],
            ['name' => 'Sticker Pack Exclusivo', 'price' => 3, 'stock' => 200],
            ['name' => 'Lanyard & Porta-Crachá', 'price' => 4, 'stock' => 150],
            ['name' => 'Garrafa de Água Inox', 'price' => 18, 'stock' => 40],
        ];

        $products = collect($productsData)->map(function ($p) use ($edition) {
            return Product::create([
                'edition_id' => $edition->id,
                'name' => $p['name'],
                'price' => $p['price'],
                'stock' => $p['stock'],
            ]);
        });

        $this->command->info('Creating competitions, teams and prizes');
        $competitionsConfig = [
            [
                'name' => 'Hackathon SINF',
                'slug' => 'hackathon-sinf',
                'theme' => 'Desenvolvimento de Soluções Sustentáveis e Inovadoras',
                'description' => 'O Hackathon SINF é a maratona de desenvolvimento de software icónica da Semana de Informática. Em equipas de 2 a 4 elementos, os participantes têm 48 horas para planear, desenhar e implementar um protótipo funcional para resolver desafios reais propostos por empresas de referência.',
                'date_start' => Carbon::create($editionYear, 11, 16, 10, 0, 0),
                'date_end' => Carbon::create($editionYear, 11, 18, 18, 0, 0),
                'registration_link' => 'https://sinf.pt/competicoes/hackathon-sinf',
                'regulation' => "### Regulamento do Hackathon SINF\n\n1. **Equipas**: Cada equipa deve ser composta por 2 a 4 participantes devidamente inscritos na SINF.\n2. **Duração**: Início a 16 de novembro às 10:00 e entrega final a 18 de novembro às 18:00.\n3. **Avaliação**: Os projetos serão avaliados por um júri composto por docentes da FEUP e profissionais convidados, tendo em conta:\n   - **Inovação e Originalidade** (30%)\n   - **Viabilidade Técnica e Qualidade do Código** (35%)\n   - **Impacto e Utilidade da Solução** (20%)\n   - **Apresentação e Pitch Final** (15%)\n4. **Propriedade Intelectual**: Todo o código desenvolvido pertence exclusivamente aos membros da equipa.",
                'teams' => [
                    ['name' => 'ByteBuilders', 'points' => 950],
                    ['name' => 'NullPointers', 'points' => 880],
                    ['name' => 'CodeForge', 'points' => 820],
                    ['name' => 'KernelHacks', 'points' => 760],
                    ['name' => 'StackCrushers', 'points' => 690],
                ],
            ],
            [
                'name' => 'Capture The Flag (CTF)',
                'slug' => 'ctf-sinf',
                'theme' => 'Segurança Ofensiva e Análise Forense',
                'description' => 'Testa os teus conhecimentos e astúcia em cibersegurança numa competição de estilo Jeopardy! Desafios de Web Exploitation, Criptografia, Reverse Engineering, Forensics e Pwn para todos os níveis.',
                'date_start' => Carbon::create($editionYear, 11, 17, 14, 0, 0),
                'date_end' => Carbon::create($editionYear, 11, 17, 21, 0, 0),
                'registration_link' => 'https://sinf.pt/competicoes/ctf-sinf',
                'regulation' => "### Regulamento do CTF SINF\n\n1. **Formato**: Competição individual ou em duplas no formato Jeopardy.\n2. **Duração**: 17 de novembro, das 14:00 às 21:00.\n3. **Regras de Conduta**:\n   - É expressamente proibido atacar a infraestrutura da competição fora dos alvos designados.\n   - Não é permitida a partilha de flags entre equipas concorrentes.\n   - O desrespeito pelas regras resultará em desqualificação imediata.\n4. **Pontuação**: Cada desafio tem uma pontuação dinâmica baseada no número de resoluções bem-sucedidas.",
                'teams' => [
                    ['name' => 'CyberWardens', 'points' => 1450],
                    ['name' => 'RootAccess', 'points' => 1320],
                    ['name' => 'FlagHunters', 'points' => 1100],
                    ['name' => 'ZeroDaySquad', 'points' => 950],
                    ['name' => 'PacketSniffers', 'points' => 780],
                ],
            ],
            [
                'name' => 'Game Jam SINF',
                'slug' => 'game-jam',
                'theme' => 'Criação de Videojogos em 72 Horas',
                'description' => 'Dá asas à tua criatividade! Cria um videojogo completo num fim de semana com base num tema surpresa anunciado na sessão inaugural. Motores de jogo livres (Godot, Unity, Unreal, WebGL).',
                'date_start' => Carbon::create($editionYear, 11, 16, 12, 0, 0),
                'date_end' => Carbon::create($editionYear, 11, 19, 16, 0, 0),
                'registration_link' => 'https://sinf.pt/competicoes/game-jam',
                'regulation' => "### Regulamento da Game Jam SINF\n\n1. **Tema**: O tema é revelado no primeiro dia da SINF e todos os jogos devem integrar o conceito.\n2. **Motores e Assets**:\n   - Podem ser utilizados quaisquer motores ou bibliotecas de código aberto/livres.\n   - Assets de terceiros (música, texturas, modelos 3D) devem ter licença permissiva e ser devidamente creditados.\n3. **Submissão**: Os jogos devem ser submetidos na página do Itch.io da SINF com build executável ou jogável no browser.\n4. **Júri e Votação**: Avaliação baseada em Jogabilidade, Arte e Som, Criatividade e Aderência ao Tema.",
                'teams' => [
                    ['name' => 'PixelCrafters', 'points' => 94],
                    ['name' => 'GlitchStudio', 'points' => 88],
                    ['name' => 'PolyGonDevs', 'points' => 81],
                    ['name' => 'SpriteMinds', 'points' => 75],
                    ['name' => 'IndieWizards', 'points' => 67],
                ],
            ],
        ];

        $competitions = collect($competitionsConfig)->map(function ($compConfig) use ($edition, $participants) {
            $competition = Competition::create([
                'edition_id' => $edition->id,
                'name' => $compConfig['name'],
                'slug' => $compConfig['slug'],
                'theme' => $compConfig['theme'],
                'description' => $compConfig['description'],
                'date_start' => $compConfig['date_start'],
                'date_end' => $compConfig['date_end'],
                'registration_link' => $compConfig['registration_link'],
                'regulation' => $compConfig['regulation'],
            ]);

            // Create 3 prizes (1st, 2nd, 3rd place)
            for ($place = 1; $place <= 3; $place++) {
                CompetitionPrize::create([
                    'competition_id' => $competition->id,
                    'place' => $place,
                ]);
            }

            // Create teams and attach participants
            foreach ($compConfig['teams'] as $teamData) {
                $team = CompetitionTeam::create([
                    'competition_id' => $competition->id,
                    'name' => $teamData['name'],
                    'points' => $teamData['points'],
                ]);

                // Attach 2 to 4 participants
                $members = $participants->random(rand(2, 4));
                $team->members()->attach($members);
            }

            return $competition;
        });

        $this->command->info('Creating slots and quests');
        $slotsData = [
            ['name' => 'Primeiro Passo', 'total_quests' => 1, 'points' => 10],
            ['name' => 'Madrugador', 'total_quests' => 2, 'points' => 15],
            ['name' => 'Veterano da SINF', 'total_quests' => 2, 'points' => 20],
            ['name' => 'Espreita a Agenda', 'total_quests' => 2, 'points' => 12],
            ['name' => 'Explorador de Bancas', 'total_quests' => 3, 'points' => 25],
            ['name' => 'Aficionado de Palestras', 'total_quests' => 3, 'points' => 20],
            ['name' => 'Mestre do Networking', 'total_quests' => 3, 'points' => 25],
            ['name' => 'Colecionador de QR Codes', 'total_quests' => 4, 'points' => 30],
            ['name' => 'Caçador de Tarefas', 'total_quests' => 4, 'points' => 30],
            ['name' => 'Veterano de Workshops', 'total_quests' => 4, 'points' => 35],
            ['name' => 'Maratonista SINF', 'total_quests' => 5, 'points' => 40],
            ['name' => 'Lenda da SINF', 'total_quests' => 6, 'points' => 50],
        ];

        $slots = collect($slotsData)->map(fn (array $slot) => Slot::create([
            'name' => $slot['name'],
            'total_quests' => $slot['total_quests'],
            'points' => $slot['points'],
        ]));

        $standQuests = $stands->unique('sponsor_id')->map(function ($stand) use ($edition, $slots) {
            $sponsorName = $stand->sponsor?->company?->user?->name ?? 'Empresa';
            $quest = Quest::create([
                'name' => "Visitar a banca da {$sponsorName}",
                'category' => 'COMPANY',
                'requirement_id' => $stand->id,
                'requirement_type' => Stand::class,
                'edition_id' => $edition->id,
            ]);
            if ($slots->isNotEmpty()) {
                $quest->slots()->attach($slots->random(min(2, $slots->count())));
            }

            return $quest;
        });

        $eventQuests = $events->map(function ($event) use ($edition, $slots) {
            $category = $event->type->name === 'talk' ? 'TALK' : 'WORKSHOP';
            $quest = Quest::create([
                'name' => "Assistir a: {$event->name}",
                'category' => $category,
                'requirement_id' => $event->id,
                'requirement_type' => Event::class,
                'edition_id' => $edition->id,
            ]);
            if ($slots->isNotEmpty()) {
                $quest->slots()->attach($slots->random(min(2, $slots->count())));
            }

            return $quest;
        });

        $allQuests = $standQuests->concat($eventQuests);

        $this->command->info('Creating enrollments');
        $participants->random(40)->each(function ($participant) use ($edition, $events, $products, $allQuests) {
            $enrollment = Enrollment::create([
                'edition_id' => $edition->id,
                'participant_id' => $participant->id,
            ]);

            $enrollment->events()->attach($events->random(rand(2, 5)));
            $enrollment->quests()->attach($allQuests->random(rand(3, 8)));
            $enrollment->products()->attach($products->random(rand(1, 3)));

            // The product attach above already deducted its price from the
            // balance via a DB trigger, but quest/slot unlocks grant too few
            // points organically, leaving most balances negative. Set an
            // explicit, varied positive balance so the shop is actually usable
            // and so both the "can afford" and "insufficient funds" states
            // are easy to demo.
            $enrollment->points = rand(5, 150);
            $enrollment->save();
        });

        $this->command->info('Seeding finished successfully!');
    }
}
