import type Model from "@/Types/Model";
import type Company from "@/Types/Company";

export default interface Sponsor extends Model {
    tier: "PLATINUM" | "GOLD" | "SILVER";
    company_id: number;
    edition_id: number;

    company: Company;
}
