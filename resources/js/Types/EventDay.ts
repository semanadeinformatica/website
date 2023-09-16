import type Model from "./Model";

export default interface EventDay extends Model {
    date: Date;
    theme: string;
    edition_id: number;
}
