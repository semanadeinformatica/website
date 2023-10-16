import type Company from "./Company";
import type Edition from "./Edition";
import type Model from "./Model";
import type SponsorTier from "./SponsorTier";

export default interface Sponsor extends Model {
    company_id: number;
    company?: Company;

    edition_id: number;
    edition?: Edition;

    sponsor_tier_id: number;
    tier?: SponsorTier;
}
