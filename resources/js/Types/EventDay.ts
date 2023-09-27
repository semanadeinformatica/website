import type Model from "@/Types/Model";
import type Stand from "@/Types/Stand";
import type Event from "@/Types/Event";

export default interface EventDay extends Model {
    date: Date;
    theme: string;
    edition_id: number;

    stands?: Stand[];

    events?: Event[];
    talks?: Event[];
    workshops?: Event[];
}
