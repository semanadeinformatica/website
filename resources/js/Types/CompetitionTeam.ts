import type Model from "@/Types/Model";
import type Student from "@/Types/Student";
import type Competition from "@/Types/Competition";

export default interface CompetitionTeam extends Model {
    name: string;
    points: number;

    students?: Student[];
    
    competition_id: number
    competition?: Competition;
}
