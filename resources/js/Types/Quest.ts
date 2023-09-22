import type Model from "./Model";

export default interface Quest extends Model {
    name: string;
    category: "COMPANY" | "TALK" | "WORKSHOP" | "MILESTONE" | "TEAMBUILDING";
    requirement_type: string;
    requirement_id: number;
    requirement?: unknown;
    edition_id: number;
}
