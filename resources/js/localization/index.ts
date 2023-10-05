import type { I18nOptions } from "vue-i18n";

const messages = {
    en: {
        pages: {
            home: {
                title: "Home",
                comingSoonNews: "A lot of news. Don't miss out...",
                aboutUs: {
                    label: "About us",
                    text1: "SINF (Semana de Informática), organized by NIAEFEUP (Núcleo de Informática da Associação de Estudantes da Faculdade de Engenharia da Universidade do Porto), was created with the purpose of allowing students to, regardless of their degree, develop their skills in the various fields related to Informatics, promoting their interaction with the corporate world through a series of social gatherings.",
                    text2: "2023's edition runs through the 25th until the 31st of october. Participants are presented with talks and workshops where they can directly contact with several technologies and concepts that do not integrate their academic journey, therefore focusing on their technical skills. Besides this, there are also a pitch, mock interview sessions and company visits, nurturing the contact between participants and nationally and internationally acclaimed businesses, and as such developing their soft skills",
                },
                thisYear: "This year we have...",
                speakers: "Speakers",
                sponsors: {
                    label: "Sponsors",
                    tier: {
                        plat: "Platinum",
                        gold: "Gold",
                        silver: "Silver",
                    },
                },
            },
            program: {
                title: "Program",
                header: "Program",
            },
            maintenance: {
                title: "Coming soon...",
                date: "25th - 31th october",
                prepareEdition: "We are preparing another year of SINF",
                contactUs: "Contact us",
            },
            event: {
                title: "Event",
                join: "Join",
                callToAction: "Let's do this?",
                seeYou: "See you there!",
                notEnrolled: "You still haven't enrolled for SINF!",
                spots: {
                    pre: "We still have",
                    pos: "spot | spots",
                },
                full: "Full attendance",
                stands: "Stands",
                workshops: "Workshops",
            },
            competition: {
                title: "Competition",
                rules: "Rules",
                callToAction: "Let's do this?",
                join: "Join!",
            },
            profile: {
                title: "Profile",
            },
            team: {
                title: "Team",
                header: "Team",
            },
            error: {
                back: "Back",
                moreInfo: "More info",
            },
            shop: {
                title: "Shop",
                header: "Shop",
            },
        },
        components: {
            program: {},
            navbar: {
                navLinks: {
                    aboutus: "About Us",
                    speakers: "Speakers",
                    program: "Program",
                    team: "Team",
                    sponsors: "Sponsors",
                    contacts: "Contacts",
                    shop: "Shop",
                },
                competitions: "Competitions",
                profileDropdown: {
                    profile: "Profile",
                    admin: "Admin Dashboard",
                    logout: "Logout",
                },
            },
            footer: {
                copyrightNotice: "all rights reserved",
            },
        },
        events: {
            days: "days",
            stalls: "stalls",
            talks: "talks",
            workshops: "workshops",
        },
        general: {
            to: "to",
            sinfLogoAlt: "Stylized SINF logo",
            soon: "Coming soon...",
        },
        models: {
            admin: "Admin | Admins",
            company: "Company | Companies",
            competition: "Competition | Competitions",
            competitionTeam: "Competition Team | Competition Teams",
            department: "Department | Departments",
            edition: "Edition | Editions",
            enrollment: "Enrollment | Enrollments",
            event: "Event | Events",
            eventDay: "Event Day | Event Days",
            participant: "Participant | Participants",
            product: "Product | Products",
            quest: "Quest | Quests",
            slot: "Quest Slot | Quest Slots",
            socialMedia: "Social Media | Social Media",
            speaker: "Speaker | Speakers",
            sponsor: "Sponsor | Sponsors",
            staff: "Staff | Staff",
            stand: "Stand | Stands",
            user: "User | Users",
            eventType: "Event Type | Event Types",
        },
    },
    pt: {
        pages: {
            home: {
                title: "Página Principal",
                comingSoonNews: "Muitas novidades para ti! Está quase...",
                aboutUs: {
                    label: "Sobre nós",
                    text1: "A Semana de Informática (SINF), organizada pelo Núcleo de Informática da Associação de Estudantes da Faculdade de Engenharia da Universidade do Porto (NIAEFEUP), foi criada com o intuito de permitir aos estudantes, independentemente do curso, desenvolver as suas capacidades nas diversas áreas da Informática, promovendo a sua interação com o mundo empresarial através de eventos sociais.",
                    text2: "A edição de 2023 decorre entre os dias 25 e 31 de outubro. Os participantes têm a seu dispor palestras e workshops onde entrarão em contacto com diversas tecnologias e conceitos que não intervêm no percurso académico, focando-se portanto nas skills técnicas, assim como um pitch, sessões de entrevistas e visitas a empresas, fomentando o seu contacto com empresas de topo a nível nacional e internacional, podendo desenvolver as suas soft skills.",
                },
                thisYear: "Este ano temos...",
                speakers: "Palestrantes",
                sponsors: {
                    label: "Patrocínios", // Parceiros ?
                    tier: {
                        plat: "Platina",
                        gold: "Ouro",
                        silver: "Prata",
                    },
                },
            },
            program: {
                title: "Programa",
                header: "Programa",
            },
            maintenance: {
                title: "Brevemente...",
                date: "25 a 31 de outubro",
                prepareEdition: "Estamos a preparar mais uma edição da SINF!",
                contactUs: "Contacta-nos!",
            },
            event: {
                title: "Evento",
                join: "Inscreve-te",
                callToAction: "Vamos a isto?",
                seeYou: "Vemo-nos na lá!",
                notEnrolled: "Ainda não te inscreveste na SINF!",
                spots: {
                    pre: "Ainda temos",
                    pos: "lugar | lugares",
                },
                full: "Evento esgotado",
                stands: "Bancas",
                workshops: "Atividades",
            },
            competition: {
                title: "Competição",
                rules: "Regulamento",
                callToAction: "Vamos a isto?",
                join: "Participar!",
            },
            profile: {
                title: "Perfil",
            },
            team: {
                title: "Equipa",
                header: "Equipa",
            },
            error: {
                back: "Voltar",
                moreInfo: "Mais informações",
            },
            shop: {
                title: "Loja",
                header: "Loja",
            },
        },
        components: {
            program: {},
            navbar: {
                navLinks: {
                    aboutus: "Sobre nós",
                    speakers: "Palestrantes",
                    program: "Programa",
                    team: "Equipa",
                    sponsors: "Patrocínios",
                    contacts: "Contactos",
                    shop: "Loja",
                },
                competitions: "Competições",
                profileDropdown: {
                    profile: "Perfil",
                    admin: "Administração",
                    logout: "Logout",
                },
            },
            footer: {
                copyrightNotice: "todos os direitos reservados",
            },
        },
        events: {
            days: "dias",
            stalls: "bancas",
            talks: "palestras",
            workshops: "workshops",
        },
        general: {
            to: "a",
            sinfLogoAlt: "Logótipo da SINF",
            soon: "Em breve...",
        },
        models: {
            admin: "Administrador | Administradores",
            company: "Empresa | Empresas",
            competition: "Competição | Competições",
            competitionTeam: "Equipa de Competição | Equipas de Competição",
            department: "Departamento | Departamentos",
            edition: "Edição | Edições",
            enrollment: "Inscrição | Inscrições",
            event: "Evento | Eventos",
            eventDay: "Dia de Evento | Dias de Evento",
            participant: "Participante | Participantes",
            product: "Produto | Produtos",
            quest: "Tarefa | Tarefas",
            slot: "Encaixe de Tarefa | Encaixes de Tarefa",
            socialMedia: "Redes Sociais | Redes Sociais",
            speaker: "Orador | Oradores",
            sponsor: "Patrocinador | Patrocinadores",
            staff: "Staff | Staff",
            stand: "Banca | Bancas",
            user: "Utilizador | Utilizadores",
            eventType: "Tipo de Evento | Tipos de Evento",
        },
    },
};

const config: I18nOptions = {
    legacy: false,
    globalInjection: true,
    locale: "pt",
    fallbackLocale: "en",
    messages,
    datetimeFormats: {
        en: {
            short: {
                year: "numeric",
                month: "numeric",
                day: "numeric",
            },
            long: {
                month: "long",
                day: "numeric",
            },
            longYear: {
                year: "numeric",
                month: "long",
                day: "numeric",
            },
        },
        pt: {
            short: {
                year: "numeric",
                month: "numeric",
                day: "numeric",
            },
            long: {
                month: "long",
                day: "numeric",
            },
            longYear: {
                year: "numeric",
                month: "long",
                day: "numeric",
            },
        },
    },
};

export default config;
