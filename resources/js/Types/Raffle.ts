import type Model from "./Model";

export default interface RaffleDraw extends Model {
    edition_id: number;
    required_event_id: number | null;
    required_event?: {
        id: number;
        name: string;
    } | null;
    name: string;
    description: string | null;
    ticket_price: number;
    is_active: boolean;
    draw_at: string | null;
    winner_enrollment_id: number | null;
    total_tickets?: number;
    winner?: {
        id: number;
        participant?: {
            user?: {
                id: number;
                name: string;
                email?: string;
                profile_photo_url?: string;
            };
        };
    };
}

export interface EligibleParticipant {
    id: number;
    name: string;
    email: string | null;
    points: number;
    tickets: number;
}

export interface RaffleTicketMap {
    [drawId: number]: number;
}
