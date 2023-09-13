import type Model from "./Model";

export default interface Quest extends Model {
    name: string;
    points: number;
    category: "COMPANY" | "TALK" | "WORKSHOP" | "MILESTONE" | "TEAMBUILDING";
    edition_id: number;
}
