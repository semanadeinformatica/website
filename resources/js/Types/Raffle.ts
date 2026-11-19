import type Model from "./Model";

export default interface RaffleDraw extends Model {
    edition_id: number;
    required_event_id: number | null;
    required_event?: {
        id: number;
        name: string;
    } | null;
    required_quest_id: number | null;
    required_quest?: {
        id: number;
        name: string;
    } | null;
    place: number | null;
    name: string;
    description: string | null;
    ticket_price?: number;
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
}

export interface SimulationPerson {
    id: number;
    name: string;
}

export interface SimulationPrize {
    id: number;
    place: number | null;
    name: string;
    description: string | null;
    image_url: string | null;
    winner: SimulationPerson | null;
    is_simulated: boolean;
    eligible_count: number;
    pool: SimulationPerson[];
}

export interface RaffleTicketMap {
    [drawId: number]: number;
}
