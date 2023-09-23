import type Model from "@/Types/Model";
import type Edition from "@/Types/Edition";
import type CompetitionTeam from "@/Types/Competition";

export default interface Competition extends Model {
    name: string;
    slug: string;
    theme: string;
    date_start: string;
    date_end: string;
    registration_link: string;

    edition_id: number;
    edition?: Edition;

    teams?: CompetitionTeam[];
}
