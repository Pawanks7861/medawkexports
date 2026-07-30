<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    :root {
        --fg: #111827;
        --muted: #4b5563;
        --border: #e5e7eb;
    }

    /* body {
        font-family: "Times New Roman", serif;
        color: var(--fg);
        line-height: 1.5;
        margin: 24px;
    } */

    h1,
    h2,
    h3 {
        margin: 0 0 8px;
    }

    h1 {
        text-align: center;
        text-decoration: underline;
        font-size: 22px;
        margin-bottom: 12px;
    }

    .subtitle {
        text-align: center;
        font-style: italic;
        color: var(--muted);
        margin-bottom: 24px;
    }

    p {
        margin: 8px 0;
    }

    .whereas {
        margin: 14px 0;
    }

    .section-title {
        font-weight: bold;
        text-decoration: underline;
        margin: 16px 0 8px;
    }

    .pair {
        display: flex;
        gap: 12px;
    }

    .pair>div {
        flex: 1;
    }

    .hr {
        border-top: 1px solid var(--border);
        margin: 16px 0;
    }

    ol {
        padding-left: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
    }

    th,
    td {
        border: 1px solid var(--border);
        padding: 6px 8px;
        vertical-align: top;
    }

    th {
        text-align: left;
    }

    .sign-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }

    .sign-box {
        min-height: 120px;
        border: 1px dashed var(--border);
        padding: 12px;
    }

    .small {
        font-size: 13px;
        color: var(--muted);
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            margin: 10mm;
        }
    }

    strong {
        font-weight: 700px;
        color: black;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('purchase/customer/' . $customer['userid']), array('id' => 'sale-agreement-form')); ?>
                        <input type="hidden" name="customer_id" value="<?php echo $customer['userid']; ?>">
                        <input type="hidden" name="sale_agreements" value="1">
                        <input type="hidden" name="agreement_master_id" value="<?php echo isset($master_id) ? $master_id : ''; ?>">
                        <!-- <input type="text" name="agreement_name" class="form-control " placeholder="Agreement Name" value="<?php echo isset($customer) ? $customer['agreement_name'] : ''; ?>"> -->
                        <br>
                        <?php $date_value = (isset($documentation) ? $documentation[0]['date'] : '') ?>
                        <?php $month_value = (isset($documentation) ? $documentation[0]['month'] : '') ?>
                        <?php $year_value = (isset($documentation) ? $documentation[0]['year'] : '') ?>
                        <h1>SALE DEED</h1>
                        <div class="subtitle">Kautilya OnE-54 • RERA No. PR/GJ/AHMEDABAD/AHMEDABAD CITY/AUDA/MAA10980/291122</div>
                        <?php
                        $block_name = get_block_name($customer['block_id']);
                        $flat_name = get_flat_name($customer['flat_id']);
                        $floor_name = get_floor_name($customer['floor_id']);
                        $banakhat_details = get_banakhat_details($customer['property_id'], $flat_name, $block_name, $floor_name);
                        ?>
                        <div class="section box">
                            <p class="center">The Sale Deed of Residential <strong>Flat No.<span class="u"><?= $flat_name ?></span> </strong> in Wing <strong>“<?php echo $block_name ?>”</strong> having total Carpet Area admeasuring about <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> situated on
                                <strong><?= $floor_name ?></strong> of the said Scheme along with (i) <strong>Wash Area admeasuring<?= $banakhat_details->wash_yard ?> sq.mtrs.</strong> (ii) Balcony admeasuring about 3<strong><?= $banakhat_details->balcony ?> sq.mtrs.</strong> (under the provisions of Gujarat RERA Act) carpet area as well as approximately <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> (as per plan sanctioned by Ahmedabad Municipal Corporation) in the scheme known as “ KAUTILYA ONE-54 ” together with undivided share in the said land admeasuring about <strong><?= round($banakhat_details->undivided_land_share, 2) ?> sq.mtrs</strong> bearing A) Final Plot No. 321, admeasuring 3400 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/3 admeasuring 5666 sq. m.& B) Final Plot No. 322, admeasuring 2125 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/4 admeasuring 3541 sq. m. Thereby, a total of 5525 sq. m. of land of both the final plots now amalgamated Final Plot no: (321+322) and the affordable housing project being built on the above said land situated within the village limits of Chandkheda, Taluka - Sabarmati in the Registration Sub - District of Ahmedabad - 13 (Sabarmati) of District Ahmedabad. Sale deed of the sale consideration price <strong><?= $customer['final_amount'] ?></strong> (Rupees <strong><?= convertToIndianCurrency($customer['final_amount']) ?> only</strong>)
                            </p>
                        </div>

                        <h4>FIRST PARTY - VENDOR :-</h4>
                        <p><span>KAUTILYA DEVELOPERS</span><br> <span>PAN : AATFK 6344 G</span></p>
                        <p>A Partnership Firm, having its Registered office at : 30, Lad Society, B/h. Judges Bunglow, , Ahmedabad - 380054 & having site office at, "Kautilya One-54", located at Opp. Swaminarayan Temple, B/h. Omkar Lotus, Chandkheda, Ahmedabad.</p>
                        <p>Here in after in this Deed of Sale referred to as <strong>“ THE VENDOR”</strong> or <strong> “ THE FIRST PARTY ” </strong> which expression shall unless it be repugnant to the context or meaning thereof be deemed to mean and include the said <strong>“VENDOR ”</strong> and its present and future partners, authorized signatories, successors, agents, administrators, legal representative and assignees of the FIRST PARTY.</p>

                        <p>SECOND PARTY - PURCHASER :-</p>
                        <p>(1) <strong><?= $customer['company']; ?></strong></p>
                        [ PAN : <strong><?= $customer['pan_card'] ?></strong>]
                        [ AADHAR : <strong><?= $customer['adhar_card'] ?></strong>]<br>
                        Adult Residing at -<strong><?= $customer['address'] ?></strong><br>
                        <?php
                        if (!empty($customer2)) { ?>
                            (1) <strong><?= $customer2->company2; ?></strong></p>
                            [ PAN : <strong><?= $customer2->pan_card_2 ?></strong>]
                            [ AADHAR : <strong><?= $customer2->adhar_card_2; ?></strong>]<br>
                            Adult Residing at -<strong><?= $customer2->address_2 ?></strong>
                        <?php }

                        ?>
                        <p>Here in after in this Deed of Sale referred to as <strong>“ THE PURCHASER ”</strong> or <strong>“ THE SECOND PARTY ”</strong> which expression shall unless it be repugnant to the context or meaning thereof be deemed to mean and include the said <strong>“ PURCHASER ”</strong> and his / her / their heirs, agents, administrators, legal representative and assignees of the SECOND PARTY.</p>

                        <p>1) The developer is seized and possessed of or otherwise well sufficiently entitled to all that piece and parcel of land bearing
                            1) Final Plot No. 321, admeasuring 3400 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/3 admeasuring 5666 sq. m.& 2) Final Plot No. 322, admeasuring 2125 sq. m. of Town Planning Scheme No. 76
                            / B (Chandkheda), allotted in lieu of Survey No. 875/4 admeasuring 3541 sq. m. Thereby, a total of 5525 sq. m. of land of both the final plots now amalgamated Final Plot no: (321+322) and the affordable housing project being built on the above said land situated within the village limits of Chandkheda, Taluka - Sabarmati in the Registration Sub - District of Ahmedabad - 13 (Sabarmati) which forms the Project land for ‘Kautilya One-54’. Hereinafter referred to as “ the said land ”.</p>

                        <p>2) The Non-Agricultural Permission for Residencial & Commercial purpose of the “Said Land” was granted by the Hon' District Collector, Ahmedabad under 1) Order No. CB / NA / AHMEDABAD<br>
                            / CHANDKHEDA / 875 / 3 / 1009284 / 2019 on 03-06-2019 for
                            Survey No. 875 / 3 of Mouje Chandkheda and entry to that effect was mutated in the revenue record by mutation entry No. 13606 dated : 15-06-2019, which were certified by the competent authority on 29-07-2019 & 2) Order No. CB / LAND-1 / NA / SR - 956 / 2018 / FMPS NO - 323282 on 29-10-218 for Survey No. 875
                            / 4 of Mouje Chandkheda and entry to that effect was mutated in the revenue record by mutation entry No. 13372 dated : 15-12- 2018, which were certified by the competent authority on 28-01- 2019.
                        </p>

                        <p>3) The Vendor has purchased the Said land Paiki 1) Survey No. 875 /
                            3 from Daksh enterprise, a partnership firm by sale deed registered in the office of the Sub-Registrar of Assurances of Ahmedabad - 2 (Vadaj) under Serial No. 5406, dated : 04-04-2022 and entry to that effect was mutated in the revenue record by mutation entry No. 14858, Dated : 12-04-2022, Which was certified by the competent authority on 13-05-2022 & 2) Survey No. 875 / 4 from Jayantibhai Prahladbhai Nayak, Dilipkumar alias Bipinkumar Prahladbhai Nayak, Rajendrakumar Prahladbhai Nayak, Kailasben D/o. Prahladbhai Nayak W/o. Kundanlal Nayak, Sudhaben D/o. Prahladbhai Nayak Wd/o. Maheshbhai Nayak, Nitinkumar Nandubhai Nayak, Bhavnaben D/o. Nandubhai Nayak W/o. Nileshbhai Nayak, Rinaben D/o. Nandubhai Nayak W/o. Jitendrakumar Sisodiya, Jyotiben D/o. Nandubhai Nayak W/o. Jayendrakumar Nayak, Ranjanben Wd/o. Nandubhai Prahladbhai Nayak, Ajaykumar Mahendrakumar Nayak, Amarkumar Mahendrakumar Nayak, Dipakkumar Mahendrakumar Nayak, Ashaben D/o. Mahendrakumar Nayak W/o. Krunalkumar Nayak & Pratimaben Wd/o. Mahendrakumar Prahladbhai Nayak by sale deed registered in the office of the Sub-Registrar of Assurances of Ahmedabad - 2 (Vadaj) under Serial No. 20546, dated : 22-11-2018. In said deed of sale Sankalp Infrastructure, a partnership firm remains their presence as confirming party and entry to that effect was mutated in the revenue record by mutation entry No. 13355, Dated : 01-12-2018, Which was certified by the competent authority on 03-01-2019.</p>

                        <p>4) It is further clarified that the aforesaid NA orders together apply to the combined land identified as Final Plot No. 321 and Final Plot No. 322 (total 5,525 sq. m.). Thereafter, the said Final Plots were duly amalgamated and recognized as a single parcel by Ahmedabad Municipal Corporation under Amalgamation / Consolidation Order No. 001LD22230031 dated 07/05/2022. Ahmedabad Municipal Corporation granted permission for construction on said land by following Commencement Letter [Rajachitthi] issued on 28th July, 2022 and granted Development Permission.</p>

                        <p><strong>Block No. Case No. (Rajachitthi No.)</strong><br>
                            A + B BHNTI / WZ / 210522 / CGDCRV / A6107 / R0 / M1<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Rajachitthi No. 06627 / 210522 / A6107 / R0 / M1)<br><br>


                            C &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BHNTS / WZ / 210522 / CGDCRV / A6108 / R0 / M1<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            (Rajachitthi No. 06628 / 210522 / A6108 / R0 / M1)<br><br>


                            D &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BHNTS / WZ / 210522 / CGDCRV / A6109 / R0 / M1<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            (Rajachitthi No. 06629 / 210522 / A6109 / R0 / M1)<br><br>
                        </p>

                        <p>5) The “Said Developer” has floated scheme of Residential & Commercial units known as “ KAUTILYA ONE-54 ” (hereinafter referred to as the “Said Scheme”) on the “Said Land”.
                            Then, the said developer have completed all kind of construction work as per the approved plan and therefore Ahmedabad Municipal Corporation granted B.U. Permission on dated : 16/10/2025 for block A+B and B.U. Permission as under :
                        <table class="" style="border:unset;">
                            <thead>
                                <tr>
                                    <th style="width: 22%;border:unset;"><strong>Block No.</strong></th>
                                    <th style="width: 38%;border:unset;"><strong>Building Use Certificate Number.</strong></th>
                                    <th style="width: 22%;border:unset;"><strong>Dated</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A + B</td>
                                    <td>BUC/BHNTI/WZ/210522/CGDRV/A6107/RO/M1</td>
                                    <td>16/10/2025</td>
                                </tr>
                                <tr>
                                    <td>C</td>
                                    <td>BUC/BHNTI/WZ/210522/CGDRV/A6108/RO/M1</td>
                                    <td>17/10/2025</td>
                                </tr>
                                <tr>
                                    <td>D</td>
                                    <td>BUC/BHNTI/WZ/210522/CGDRV/A6109/RO/M1</td>
                                    <td>17/10/2025</td>
                                </tr>
                            </tbody>
                        </table>
                        The above Building Use Permissions were issued by Ahmedabad Municipal Corporation and correspond to the RERA registration for the project: RERA No. <strong>PR/GJ/AHMEDABAD/AHMEDABAD CITY/AUDA/MAA10980/291122.</strong>
                        </p>

                        <p>6) That all persons interested in purchasing any Unit in the said scheme known as <strong>“ KAUTILYA ONE-54 ”</strong> are informed and are aware and agree that all common area and common amenities of the said scheme will be occupied by <strong>“ KAUTILYA ONE-54 Housing and Commercial Co-Operative Service Society Limited (Registered No. CSA/HDC/SAHAA/2025/01653, Dated : 03/10/2025 )”</strong>, hereinafter referred to as “Service Society” in fiduciary capacity for the better maintenance of the said scheme and all unit Purchasers will have to become member of Service Society. All unit Holders have to pay maintenance Deposit and Maintenance Charges as decided by the Service Society from time to time and the Purchaser and other Unit holders are not entitled to demand their individual share in the common area and in the common amenities and facilities of the said scheme.
                        </p>

                        <p>7) PARTY OF THE FIRST PART has made available to THE PURCHASER about the details of the Project, permission for non-agricultural use of the land, development permission issued by AMC, Certificates of Title Clearance & all other relevant documents related to the project and has given specifications of the Said Unit, details in respect of the common infrastructure, amenities, services of / in the said Project for the common use of the premises holders to form part of the said Project. THE PURCHASER has perused, studied, satisfied, agreed and has made aware himself about the same to his full satisfaction.</p>

                        <p>8) Thereafter the Second Party contacted the First Party and desired to purchase <strong>Unit No. <?= $flat_name ?></strong> in <strong>Wing “ <?= $block_name ?> ”</strong> having total Carpet Area admeasuring about <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> situated on <strong>“ <?= $floor_name ?> ”</strong> of the said Scheme along with (i) Wash Area admeasuring <strong><?= $banakhat_details->wash_yard ?> sq.mtrs.</strong>. (ii) Balcony admeasuring about <strong><?= $banakhat_details->balcony ?> sq.mtrs.</strong> (under the provisions of Gujarat RERA Act) carpet area as well as approximately <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> (as per plan sanctioned by Ahmedabad Municipal Corporation) in the scheme known as “ KAUTILYA ONE-54 ” together with undivided share in the said land admeasuring about <strong> <?= round($banakhat_details->undivided_land_share, 2) ?> Sq.Mtrs.</strong> bearing A) Final Plot No. 321, admeasuring 3400 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/3 admeasuring 5666 sq. m.&<br>
                            b) Final Plot No. 322, admeasuring 2125 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/4 admeasuring 3541 sq. m. Thereby, a total of 5525 sq. m. of land of both the final plots now amalgamated Final Plot no: (321+322) and the affordable housing project being built on the above said land situated within the village limits of Chandkheda, Taluka - Sabarmati in the Registration Sub - District of Ahmedabad - 13 (Sabarmati). The Unit and right to use common amenities, membership and share certificate right of Service Society shall be referred to as “ THE SAID PROPERTY ” OR “ THE SAID UNIT ” OR “ THE SAID FLAT ” hereinafter in this Sale-Deed and more particularly described in the Schedule.
                        </p>

                        <p>[a] The Purchaser hereby agrees to purchase from the Vendor and the Vendor hereby agrees to sell the Purchaser <strong>Unit No. <?= $flat_name ?></strong> in <strong>Wing “ <?= $block_name ?> ”</strong> having total carpet area admeasuring <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> on <strong>“ <?= $floor_name ?> ”</strong> (under the provisions of Gujarat RERA Act) carpet area as well as approximately F sq. m. (as per plan sanctioned by Ahmedabad Municipal Corporation) in the scheme known as <strong>“ KAUTILYA ONE-54 ”</strong> for the consideration of Rs. U /- along with facilities appurtenant to the Unit, the nature, extent and description of common areas and facilities like Lift, Staircase, Passage, Foyer, Underground Water Tank, Overhead Water Tank,
                            Open Terrace, Roads, Open Space, Parking, Fire Safety Equipment / System, C.C.T.V. Camera, Security System.

                        </p>

                        <p>[b] The Purchaser hereby agrees to purchase from the Vendor and the Vendor hereby agrees to sell to the Purchaser balcony having area admeasuring <strong><?= $banakhat_details->balcony ?> sq.mtrs.</strong> forming part of the said Unit/Flat and consideration of the same is included in total consideration.</p>

                        <p>[c] The Purchaser hereby agrees to purchase from the Vendor and the Vendor hereby agrees to sell to the Purchaser wash area admeasuring <strong><?= $banakhat_details->wash_yard ?> sq.mtrs.</strong> forming part of the said Unit and consideration of the same is included in total consideration.
                            <br>
                            The area of [b] balcony having area admeasuring <strong><?= $banakhat_details->balcony ?> sq.mtrs.</strong> and<br> [c] wash area admeasuring <strong><?= $banakhat_details->wash_yard ?> sq.mtrs.</strong> is included in total Carpet area admeasuring <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong>(under the provisions of Gujarat RERA Act) carpet area as well as approximately <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> (as per plan sanctioned by Ahmedabad Municipal Corporation) of said Unit.
                        </p>

                        <p>[e] In the said scheme It is decided that unit No. A-101, A-102, A-103, A- 104, B-101, B-102, B-103, B-104, C-101, C-102, C-103, C-104, D-101,D-102, D-103 & D-104 are agreed to be allotted to the respective Unit holders for their permanent individual right to use of the said open terrace as ingress and outgress of the said terrace has been given from the respective Unit as per the approved plans. The Purchaser and other members of the scheme have unconditionally agreed to the said arrangement and additional right to use to the respective Unit-holders.
                        </p>


                        <p>[f] Thereafter the First Party and Second Party have executed Agreement for Sale of said Unit which was registered before Sub-Registrar of Ahmedabad - 2 (Vadaj)/ Ahmedabad-13 (Sabarmati) under Sr. No. ______, dated__________, herein after referred to as “ The said Agreement ”.</p>

                        <p>[g] AND WHEREAS as per the terms and conditions mentioned in the said Agreement the Vendor has agreed to sell to the Purchaser and the Purchaser has agreed to purchase from Vendor the said property for a consideration of Rs.<strong><?= $customer['final_amount'] ?></strong>/- (Rupees <strong><?= convertToIndianCurrency($customer['final_amount']) ?> Only</strong>).</p>

                        <p>[h] THE PURCHASER has no complaint, dispute or grievance regarding amounts paid by them to THE SELLER in the matter of acquisition of the Said Premises and in all matters relating to the said Project- Scheme, its common amenities, facilities and services, in general. THE PURCHASER has been given receipts for all the amounts paid by him. No payment has been made by THE PURCHASER for which no receipt has been given. THE PURCHASER has agreed that no claim for any payment made by him shall be valid unless receipt for the same is produced - issued by THE SELLER or its agent. The payment particulars made by THE PURCHASER are as follows :-
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Amount (Rs.)</th>
                                    <th style="width: 38%;">Particular</th>
                                    <th style="width: 22%;">Bank Name</th>
                                    <th style="width: 18%;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <td><?= $customer['amount'] ?></td>
                                <td><?= convertToIndianCurrency($customer['amount']) ?></td>
                                <td><?= $customer['bank_name'] ?></td>
                                <td>
                                    <?php
                                    if (!empty($customer['payment_date']) && $customer['payment_date'] != '0000-00-00') {
                                        echo date('d M, Y', strtotime($customer['payment_date']));
                                    } else {
                                        echo ''; // or whatever placeholder you prefer
                                    }
                                    ?>
                                </td>
                                <tr>
                                    <th colspan="3" class="right">TOTAL CONSIDERATION: <?= $customer['amount'] ?>/- (<?= convertToIndianCurrency($customer['amount']) ?> only)</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </tbody>
                        </table>
                        There is no other any type of consideration for sale deed of the said Premises not appearing on record, paid or agreed to be paid by THE PURCHASER to THE SELLER.
                        </p>

                        <p>NOW THIS INDENTURE WITNESSETH THAT IN pursuance of this Sale Deed and in consideration of the said amount by Purchaser to the Vendor on or before the execution of these presents being the full consideration agreed to be paid, the receipt whereof the Vendor hereby admits and acknowledges and of and from the same and every part thereof forever acquit release and discharge the Purchaser, the Vendor hereby grant, convey, transfer and assure unto the Purchaser ALL THAT property of Unit, undivided land and Unit more particularly described in the schedule hereunder written TOGETHER WITH undivided right in all and singular sewers, drains, passage, common gullies, water, water-courses, lights liberties, privileges, easements, profit, advantages, right and appurtenance whatsoever to the said Unit, hereditaments and Unit or any part thereof belonging or in any way appertaining to or with the same or any part thereof now hath or any time heretofore usually held, used, occupied or enjoyed or reputed or known as part thereof and to belong or be appurtenant thereto.</p>

                        <strong>
                            <p>NOW IT IS HEREBY AGREED BY & BETWEEN THE PARTIES HERETO AS FOLLOWS :-</p>
                        </strong>

                        <ul>
                            <ol type="1">
                                <li>1. The recitals above form an integral part of these presents, and are not repeated in the operative part only for the sake of brevity and to avoid repetition, and should be deemed to have been incorporated in the operative part of these presents, as if the same were reproduced herein verbatim.</li>
                                <li>2. THE PURCHASER declares that this Sale deed has been executed by the Purchaser out of his free will and consent and understanding full meaning and implications of the provisions contained herein.</li>
                                <li>3. THE SELLER has immediately before the execution hereof handed over to THE PURCHASER his / their possession of the Said Premises duly completed in all respect, and in a good, proper condition with sanitary fixtures, hardware fittings, electrical wiring and all other required facilities, services and amenities as per the plans, specifications and designs accepted by THE PURCHASER. The Purchaser hereby confirms that he/she/they have personally visited the project site, verified all title documents, permissions and quality of construction to their full satisfaction, and have accepted peaceful and vacant possession of the said unit.</li>
                                <li>4. The Said Premises is part of the scheme. The same is sold to THE PURCHASER, and as per the rules, regulations, terms, conditions, provisions and stipulations of the scheme approved and accepted by THE PURCHASER at the time of Sale deed of the same, recorded in a form of agreement, approved, accepted and confirmed by THE PURCHASER, also separated recorded simultaneously with the execution hereof, and the same are treated as part and parcel hereof as if incorporated herein verbatim. THE PURCHASER, of the said scheme hereby agrees to observe and abide by the rules, regulations and byelaws of the scheme, including the rules and regulations for use and enjoyment of the common amenities, facilities, conveniences and structures erected at the cost of all the Purchasers for their common use and enjoyment.</li>
                                <li>5. THE PURCHASER hereby declares that the construction of the Said Premises and the said Project - Scheme in general is in accordance with the plans, specifications, design and detailed drawings, seen and agreed by THE PURCHASER. THE PURCHASER hereby confirms and records that they have no complaint or grievance for the materials used in the construction of the said Premises and the said Project-Scheme in general. THE PURCHASER was given effective opportunities to inspect and verify all facts and particulars through such person or person's expert in the subject as Purchaser desired, and all such opportunities were exploited and utilized by THE PURCHASER. It has been specifically agreed by THE PURCHASER that THE PURCHASER shall not be entitled to make any complaint or raise any dispute or grievance in the matter of the plans, specifications, design, and materials used in the construction of the Said Premises, the said Project-Scheme in general & also pertaining to the saleable area of the apartment.</li>
                                <li>6. THE PURCHASER, of the said scheme hereby agrees to observe and abide by the rules, regulations and byelaws of the scheme, including the rules and regulations for use and enjoyment of the common amenities, facilities, conveniences and structures erected at the cost of all the Purchasers for their common use and enjoyment.</li>
                                <li>7. AND THAT THE PURCHASER shall and may at all time hereafter peaceably and quietly enter upon have occupy, possess and enjoy the said Unit and receive the rents and profits thereof and of every part thereof to and for him / her / them use and benefit without any suit, eviction, interruption, claim or demand whatsoever from or by the VENDOR or any of its present and future Partners, authorized signatories, legal representative and assignees or any of them or claiming by, from under or in trust for their or any them.</li>
                                <li>8. THE VENDOR covenants with the purchaser that no litigation or proceedings of any nature concerning it or the said property are pending before any judicial, quasi judicial or Government authorities and that the said property is not under any acquisition, requisition or any reservation for any purpose whatsoever and that no one else has any rights including right of maintenance from and over the said property, and that no lien, charge or mortgage exists on the said property.</li>
                                <li>9. That the Purchaser agrees to abide by the rules, regulations and resolutions of the Service Society and assures that he / she / they shall not commit any breach of the same. Moreover terms and conditions of all other deeds like Agreement for Sale, Possession Declaration, etc shall also be binding upon the Purchaser and it transferee. The Purchaser agrees that he / she / they has / have not become free, independent and absolute owner of the said Unit, but the said Unit is to be occupied by him / her / them as a member of the Service Society pursuant to the Share Certificate given to him / her / them by this Deed from Service Society. Therefore the use and transfer etc. of this property shall be in accordance with the rules and regulations of the Service Society.</li>
                            </ol>
                        </ul>
                        <strong>
                            <p>THIS DEED FURTHER WITNESS and it is hereby mutually agreed by and between the parties hereto as under :</p>
                        </strong>
                        <p>The Purchaser irrevocably agrees that he / she / they has / have purchased the said Unit with condition to be a Member of Service Society and on the following terms and conditions and Purchaser hereby agree, confirm, and accept following conditions, restrictions, provisions, stipulations to be observed and performed by the Purchaser :</p>

                        <ol type="1">
                            <li>1. THAT the property known as <strong>“ KAUTILYA ONE-54 ”</strong> iis belonging to and shall always belong to all unit holders i.e. Residential and Commercial Unit Holders of the said scheme. The Purchaser will have ownership right of the Unit sold to him / her / them by Vendor. The Purchaser has to be a member of Service Society. Purchaser has to use common amenities strictly as per rules framed by Service Society.</li>
                            <li>2. THE PURCHASER accepts and confirms that the said Unit is duly complete in all respects, and in good, proper and complete condition with fixtures, fittings electrical wiring and other required amenities, facilities and services as per the plans, specifications and designs seen and approved by THE PURCHASER.</li>
                            <li>3. THE PURCHASER agrees and confirms that he / she / they have examined the quality of construction and common amenities provided in the said scheme and THE PURCHASER is fully satisfied about the same. THE PURCHASER was given effective opportunities to inspect and verify all facts and particulars through such person or persons expert in the subject as THE PURCHASER desired. THE PURCHASER declares that he / she has no complaint or grievance of any nature whatsoever for the quality of construction and the materials used.</li>
                            <li>4. It has been specifically agreed by THE PURCHASER that THE PURCHASER shall not be here after entitled to make any complaint or raise any dispute or grievance about the plans, specifications, design, materials used in the construction and workmanship of the said Unit and the said project in general.</li>
                            <li>5. The area of the said <strong>Unit No.<?= $flat_name ?> </strong> is admeasuring <strong><?= $banakhat_details->carpet_area ?> Sq.mtrs. total Carpet Area</strong> [As per Rules of Real Estate (Regulation and Development) Act, 2016].
                            </li>
                            <li>6. There is no consideration in cash or kind for the said Unit, not appearing on record, paid or given or agreed to paid or given by THE PURCHASER to THE VENDOR or the Vendors.</li>
                            <li>7. It has been specifically agreed that consideration or price fixed between THE VENDOR and PURCHASER is one and composite amount for the said Unit and the PURCHASER is not entitled for any running or separate details or particulars of land, construction, development, infrastructure, etc. THE PURCHASER is not entitled for any running or separate details or particulars of land, construction, development infrastructure etc.</li>
                            <li>8. Over and above the sale consideration the Purchaser agree to bear and pay to Vendor / Service Society, any amount in any from whatsoever levied, charged or imposed by any authority or authorities whomsoever; immediately on demand by Vendor / Service Society, from time to time, proportionately in respect of the said Unit.</li>
                            <li>9. THE PURCHASER has been conveyed the said undivided land and the said Unit. THE VENDOR has made aware to THE PURCHASER that they are proposing to dispose off the other Units in the said project to different other persons. THE VENDOR shall have right to dispose of these other Units in such manner at such consideration and on such terms and conditions, as THE VENDORS may deem fit.</li>
                            <li>10. The expression “ Said Unit ” sold or given to THE PURCHASER herein shall be read, understood, interpreted and implemented with the spirit and intention, thereof for his / her / their use, occupation and enjoyment.</li>
                            <li>11. The Purchaser has clearly understood and agreed that the Unit-Holders of Unit No. A-101, A-102, A-103, A-104, B-101, B-102, B-103, B-104, C-101, C-102, C-103, C-104, D-101, D-102, D-103 & D-104 have got ingress and outgress to the terrace. None of the other Unit-holders of the said scheme have any right on the terrace. Another extra terrace will be common for all Unit-Holders. Unit-Holder of Unit No. A-101, A- 102, A-103, A-104, B-101, B-102, B-103, B-104, C-101, C-102, C-103, C-104, D-101, D-102, D-103 & D-104 are not entitled to make any construction on said terrace. Further the purchaser has clearly understood and agreed that the unit holder of Ground Floor Flat No. A- 001 & B-001 shall have exclusive use rights with respect to open back side margin space located adjoining to their wash yards. The Purchaser agrees and confirms the said condition and in future the Prospective Purchaser will not make any dispute or demand for the said permanent arrangement. The Unitholder shall allow the First Party / Maintenance Society to use the terrace for any utilities repairs and he / she / they is / are not entitled to raise any objection for the same.</li>
                            <li>12. THE PURCHASER has satisfied themselves / himself about the title of the Said Property / Said Premises and he / she / they shall not be entitled to further investigate and no requisition or objection shall be raised in any matters relating to the same. THE PURCHASER accepts such title; THE LAND SELLER has provided for the said land, as certified by Advocate and shall not raise any objection to or dispute the Land Seller’s right, title, interest to the said land in future.The Vendor assures and declares unto the Purchaser that the said property was purchased out of the funds of Vendor and hence except the Vendor nobody else is having right, title, share, claim and interest and prior to the conveyance of the said Property, the Vendor has not sold, transferred, assigned, mortgaged or gifted the said property or any part thereof to anybody else and that there is no any order passed by any court of law restraining the Vendor from being sale, transfer, assign, mortgage of the said property to anybody else and that there are no legal proceedings standing or held on the said property by any court or authority nor any such order is issued or served by any court or authority and that the said property is not under any acquisition, requisition or reservation and that our titles to the said property are absolutely clear, marketable and saleable Except Project Loan availed from Bandhan Bank Limited for Rs.30 Crore over the said project.</li>
                            <li>13. THE VENDOR or the Service Society shall have power and authority to regulate, control manage, govern, run, restrict the aforesaid scheme as regards time, quality, quantity, purpose or other related matters. THE PURCHASER shall be bound by the same. The decision of THE VENDOR or the Service Society formed by the THE VENDOR / UNIT- HOLDERS as regards the same shall be final and binding upon THE PURCHASER. The Service Society will consider the new Purchaser as a member of said society and the said new Purchaser will have to comply with all rules and regulations.</li>
                            <li>14. Part of the said building is on the hollow plinth and the Residential Unit-holders are given 2 allotted Car Parking with Mechanical parking system without any cost and Purchaser is not entitled to raise any objection in this regard. The Purchaser herein has agreed to such arrangement and waived his / her / their right and will not raise any objection of any nature to such arrangement in the future on any ground whatsoever.</li>
                            <li>15. All the common terrace above top floor of said project shall be permanently under ownership of service society and any flat holder will not object to use the Open Terrace for any utilities repairs like overhead water tank or TV satellite dish etc. and they are not entitled to raise any objection for the same.</li>
                            <li>16. The right of the Purchaser herein shall be subject to the overall powers and authorities of First Party / Service Society, in any of the matter concerning the Unit scheme and development thereof and all amenities pertaining to the same and in particular First Party shall have absolute authority and control as regards the un-disposed Units till handing over the possession of the scheme to the Service Society and Settlement of all accounts.</li>
                            <li>17. The Purchaser shall have no claim and / or legal title with respect to any part of the said Scheme, including but not limited to its common roads, terrace, common infrastructure facilities, amenities, and services, save and except in respect of the Said Unit agreed to be conveyed for him / her / it / them, The Said Facilities shall always be of the possession of Service Society, and the Purchaser and other Unit- holders shall be permitted to use and enjoy the same as per the rules and regulation of Service Society from time to time.</li>
                            <li>18. The Purchaser shall be bound from time to time to sign papers or documents and to do all other things as Service Society may require him / her / it / them to do from time to time to safeguard the maintenance of common amenities of the said scheme and failing which the Service Society is authorized to take action to stop use of common amenities of the Unit-Holders who has committed default.<br>
                                For safeguarding the interest of service Society and other Prospective Acquires. The rules and regulations of the Service Society shall be binding to the purchaser.
                            </li>
                            <li>19.<ol type="i">
                                    <li>(i) THE PURCHASER after execution of Sale Deed shall be responsible and liable to bear and pay, at actual, all Taxes, Cesses, dues and imposition of every description of AMC, and / or any other public bodies and authorities, which directly or indirectly relate to or pertain to the said unit and undivided share in land and also to pay proportionate share of maintenance which will be maintained by the Service Society.
                                    </li>

                                    <li>(ii) All common expenses and outgoings of security, sweeping, cleaning, lighting, maintenance, repair, replacement etc., of the said project, and amenities, facilities, services, conveniences, utilities and infrastructure therein; common expenses of administrative, management, staff, personals, maintenance of accounts and records and other similar or other related matter (all common interest matters); and any other expenses of common nature, as may be fixed by the Service Society, shall be borne and paid by THE PURCHASER and purchasers of other units in said scheme.</li>
                                </ol>
                            </li>
                            <li>20. So long as the said Unit shall not be separately assessed for Taxes, water rates, electric bills, etc., the purchaser shall pay of Service Society such amount as may be fixed from time to time, in advance towards such payment, A.M.C. Taxes and other outgoings. Further, until the said Unit, can separately be assessed for payment of cost, charges and expenses, the purchaser shall continue to pay proportionate portion of such amount, cost, charges and expenses and amount to be fixed by service Society from time to time. After the Said Unit is separately assessed, then such payments will have to be made by the purchaser on actual basis.</li>
                            <li>21. The Purchaser will have undivided right to use common facilities and amenities etc. with other purchasers of the said scheme but the said facilities are used in a proper way so that other Purchasers may not have any grievance / difficulty.</li>
                            <li>22. It is hereby agreed that the Purchaser shall not put or allow to be put any Name Plate, Sign Board and / or any other kind of display of any nature, on the compound wall, gate and / or on the exterior side of the development to be planned and / or in the open space in the said Unit without the written consent of First Party / Service Society except it is provided by the First Party.</li>
                            <li>23. That the Purchaser shall use the said Unit only for residential purpose which is sanctioned by Ahmedabad Municipal Corporation as residential Units and residential unit holders will have right to use the basement parking, common plot and common amenities of residential part. The Purchaser will not carry out any commercial, industrial, hazardous or polluting activity, nor store combustible or noxious materials in the Said Unit. Any breach shall entitle the Service Society / Vendor to take necessary action including fines or restriction of common facility usage.
                            </li>
                            <li>24. In said project seller has made basements for residential unit holder parking and hence the residential unit holders will have no right to park vehicles in front of commercial units and their margin area. The Commercial unit holders will have right to park their vehicles in the front of the commercial space / shop only and hence the Commercial unit holders will have no right to enter in the residential / Basement parking area to park their vehicles or enjoy the facilities of residential part except for repairs and maintenance of common electric and water amenities. Residential unit holders shall have to park their vehicles at First cellar & Second cellar as per arrangement done by service society and as mentioned in the parking plan. All the unit holders confirms and shall have to manage that visitors of their unit park their vehicles out side of said scheme.</li>
                            <li>25. The Purchaser hereby covenants to keep the Said Unit neat, clean and tidy and saved and protected from trespasser, from being illegally used or occupied and to keep construction, sewers, drains, pipes, appurtenances belonging thereto in a good and tenable condition so as to support and protect the part of the building structure other than their said Unit/s.</li>
                            <li>26. After conveyance of the Said Unit to the Purchaser, the Purchaser shall be entitled to let, sub-let, sell, transfer, convey, mortgage, charge or in any way encumber or deal with or dispose of the Said Unit, after obtaining prior written permission of Service Society and subject to and in accordance with the terms and conditions laid down by First Party. In the event the Purchaser is desirous of selling the Said Unit he / she / It / they shall comply with the following :-

                                <ol type="i">
                                    <li>[i] The Purchaser shall pay Transfer Fee as per rules of Service Society.</li>
                                    <li>[ii] Declaration cum Indemnity Bond to be obtained from New Purchaser ensuring that all terms and conditions, otherwise binding to the Purchaser shall also be binding to the New Purchaser.</li>
                                </ol>




                            </li>
                            <li>27. THE PURCHASER shall permit THE VENDOR or to its order the Service Society and / or its surveyors and agents with or without workmen and others at all reasonable time to enter into and upon the Said Unit or any part of the building and for the purpose of making repairing, maintaining, re-building, cleaning, lighting and keeping in order and good condition all services, drains, pipes, cables, water covers, gutters, wires, or other conveniences belonging to or used for the building/s and also for the purpose of laying down, maintaining, repairing, re-constructing, replacing and testing drainage, gas and water pipe ; and electric wires and for similar or other purposes. The Purchaser shall have to repair or change any common amenities which is damage caused by purchaser or agent of purchaser. If purchaser fails to repair such damage service society shall repair such damage at cost of purchaser.</li>
                            <li>28.
                                <ol type="a">
                                    <li>a) THE PURCHASER shall not make any changes in the elevations and outside color scheme of the Said Unit and shall not decorate the exterior of his / her / their Unit other than in the manner in which the same was previously decorated.</li>
                                    <li>b) THAT the Purchaser shall not throw dirt, rubbish, garbage, trash or any other refuse or permit the same to be thrown out from his / her / it / their property in the common passages, balconies, compound or any portion of the said Scheme.</li>
                                </ol>
                            </li>
                            <li>29. THE PURCHASER shall not make any temporary or permanent additions and alterations in the structure of the Unit, not call to do anything which may cause damage or which may the structure of the Building / Unit, like slab, columns, beams, load bearing walls, etc., Similarly, THE PURCHASER shall not also, cover the balcony. THE PURCAHSER shall not hang clothes and other articles in the balcony or out-side view of building or otherwise shall not do anything which in the opinion of Service Society does not give proper decorum and decency to the Building/Project.
                            </li>
                            <li>30. THAT Purchaser shall not alter / change the size and shape of the door, windows, shutter etc. and shall not make any hole or new window to fix air conditioner and shall put the air - conditioner at the specified place and shall not damage the partition walls, common walls, flooring ceiling etc. of the said Unit.</li>
                            <li>31. THE PURCHASER shall not change, or make any holes or openings, or draw or lay any wires, cables, pipes through, or in any other manner damage, the columns, beams, slabs or RCC pardis or walls or other structural changes of the said Unit or any part of the Project.</li>
                            <li>32. THAT the Purchaser is also aware that some of the walls of this unit/flat are of single brick thickness, which may not be too much strong. If any damage is caused to such walls due to any act of his neighbors, Purchaser/s is likely to suffer damages. Purchaser agrees that he /she shall not claim it from the Vendor. Purchaser also assure to keep and maintain all walls of the unit in good conditions.</li>
                            <li>33. Similarly the leakage of water from the toilets, bathrooms and kitchen is also likely to happen in the said unit/flat as well as from the neighboring and upper units/flats. Leaked water / moisture is likely to appear on the walls of the unit and that may deteriorate the paining and plaster on the walls. Purchaser is / are aware that water is a substance which is likely to escape resulting into its leakage. Even if all safety measures are taken to seal the joints of pipes, sometimes it cannot be avoided. Leakage may be due to various reasons unconnected with construction. Use of Acids for cleanliness, vibration of heavy duty washing machines, mild earthquake jerks, hot water, hard water, rough use, etc. are likely to damage pipelines, tiles and their joints. The joints of flooring tiles and wall tiles are also likely to be damaged by such use, any damage in the unit due to leakage of water and its various other bad effects.</li>
                            <li>34. That the doors of the units which are made of wood are likely to be swollen during monsoon due to humidity / damp and thereby can cause some hardship to the purchaser. It is due to act of nature. The Purchaser shall not be entitled to claim any damages on that ground. Similarly the purchaser shall not be entitled to recover any damages due to rusting of stoppers etc. as it is usual during monsoon / passage of time.</li>
                            <li>35. The Seller has installed lift in each Block & Purchaser unconditionally agrees that the Lift facility in this building shall be used as per rules of the society. It is to be economically used. The Purchaser as well as his
                                / her / their employee shall not misuse the said lift and will take care about it and co-operate with society members / officials of the service society. One should take care that the children do not use the lift often to play. The quality of lift is good. But this is machine and it is no manufactured by the Vendor. Therefore during the use of the lift and even as a result of any defect or otherwise if any one is injured or receive other damages then the Service Society / Vendor / Seller shall not become responsible for it and purchaser and his / her / their heirs etc. shall not demand and shall not be entitled to demand such damages / compensation from First Party / Service Society. In future all such lift license shall have to compulsorily renew by service society
                                / members.
                            </li>
                            <li>36. The seller has made borewell for use of residential & commercial units holder of the said project and all units holder have to same right on borewell and have to use it mutually agreed between residential & commercial units holder. All the common maintenance of the such borewell shall have to bear by service society only. As per approved plan seller has arranged ONE percolating well in the said project, Which shall have to maintain by service society and members. According to fire safety laws seller has installed fire safety equipment, Which are currently in properly working status. In future any of such common amenities not found working properly seller shall not held liable for non working of such equipment. All the Common amenities & equipment shall have to be properly & regularly maintained by service society / members. In future all such fire equipment license shall have to be compulsorily renew by service society / members.</li>
                            <li>37. THAT THE Purchaser or his / her / their employee, agents, contractors will not at any time demolish or cause to be done any additions / alternations / modifications of whatsoever nature to the said property or any part thereof which are likely to cause damage, hazard or structural deterioration to the said Unit, building or the neighboring Unit. The Purchaser shall not be permitted to put up anything or encroaching of passages or lounges or balconies or veranda’s or make any alterations in the elevations and outside color scheme of the property (including shutters) acquired by him / her / them. The Commercial Unit holders are not entitled to cover margin space by doing additional construction or not entitled to put advertisement on the shutters of the shops.</li>
                            <li>38. THE PURCHASER shall insure and keep insured the said unit against loss or damage by fire, earthquake, riot, war, flood, civil commotion, act of god or such other risks to the full value thereof in the name of THE PURCHASER with nationalized insurance company of repute having office at Ahmedabad, and whenever required he shall produce to THE VENDOR / Service Society the policy / policies of such insurance and the receipt for the last premium paid in respect thereof. In the event of the said unit being damaged or destroyed by fire / earthquake or otherwise the purchaser shall expend the insurance money for the repair, rebuilding or reinstatement of the said unit as soon as reasonable, practical and required.</li>
                            <li>39. The letters, receipts and / or notices issued by service Society dispatched by registered post / courier to the address of the purchaser as known to Service Society, will be sufficient proof of receipt of the same by the Purchaser and shall completely and effectively discharge Service Society.</li>
                            <li>40. The Scheme shall always be known as <strong>“KAUTILYA ONE-54 ”</strong>, and this name shall not be changed in any circumstances.</li>
                            <li>41. AND THAT this Deed of Conveyance, shall be governed and construed in accordance with the RERA Act together with the rules and regulations formed thereunder and other relevant acts, rules and statues formed by competent authority from time to time. If any term of this Deed of Conveyance are found illegal, invalid or unenforceable under the RERA Act together with the rules and regulations formed thereunder or other relevant acts, rules and statues, such term shall, insofar as it is severable from the remaining Terms, be deemed omitted from these Terms and shall in no way affect the legality, validity or enforceability of the remaining Terms which shall continue in full force and effect and be binding to the Promoter as well as the Allottee. In event of any contradiction to the terms and conditions mentioned hereinabove with the relevant acts, rules and statues, the terms mentioned in such relevant acts, rules and statues shall be binding to the Promoter as well as the Allottee.</li>
                            <li>42. All right, title and interest of the Purchaser is restricted to and to be read, understood and interpreted in relation to the Said Unit only, and all other constructed-covered-un-covered-open spaces-areas-portions, open margin lands, infrastructures, developments, amenities, facilities and services shall belong to Vendor / Service Society. The Purchaser shall at no time demand partition of his / her / it / their interest from the entire Scheme. It being agreed and declared by the Purchaser that his
                                / her / its / their interest in the scheme shall be indivisible.
                            </li>
                            <li>43. The Promoter shall not have any claim on F.S.I., Additional F.S.I. and terrace rights after Building Use permission has been obtained, such rights if any will be owned by the service society of allottee.</li>
                            <li>44. The Purchaser has / have agreed, finally, to acquire legal possession and title to the said unit by obtaining conveyance from first Party. The spirit, intention, interpretation and implementation of the word “ to confer ” or “ conferment ” of the said unit to the Purchaser, in their all-grammatical sense, in this Deed shall be understood accordingly.</li>
                            <li>45. THAT the Purchaser shall maintain at his / her / their own costs the property agreed to be purchased by him / her / them in the same good condition, state and order in which it is / will be delivered to him / her / them and shall abide by all bye laws, rules and regulations of the government, the Ahmedabad Municipal Corporation and any other authorities, local bodies, and Society and shall attend to answer and be responsible for all actions and violations of any of the conditions or rules or bye laws and shall observe and perform all the terms and conditions contained in this Sale Deed.</li>
                            <li>46. If within a period of five years from the date of BU Possession of the Building, The Said Property to the Purchaser, the Purchaser brings to the notice of the Vendor, If any structural defect in the Said Property or the building in which the Said Property are situated or any defects on account of workmanship, quality or provision of service, then, whenever possible such defects shall be rectified by the Vendor at its own cost. Purchaser shall not entitle to get compensation for damage of goods in property.</li>
                            <li>47. THAT the Purchaser and persons to whom the said Unit is ultimately transferred, assigned or given possession of shall observe and perform the bye laws and / or the rules, regulations and resolutions, which the said Society may make and the additions, alternation or amendments thereto for the protection, maintenance, use and transfer of the said building, unit and other space and Unit therein and/or in the compound. They will also abide by the building rules, regulations and bye-laws for the time being of the Ahmedabad Municipal Corporation and other authorities of the government. The Purchaser and the person to whom the said Unit is let, transferred, assigned or given possession, shall observe and perform all the stipulations and conditions laid by the Society regarding the occupation and use of the building and / or the said unit or other spaces and / or parking spaces therein and shall pay the contribution regularly and punctually towards the taxes and / or expenses or other out goings in accordance with the terms of this deed and as may be decided by the Society from time to time. All the terms, conditions, stipulations and provisions of this deed shall be binding upon the transferee of the Purchasers from time to time. </li>
                            <li>48. THAT the purchaser have inspected the unit, verified / checked all fittings and fixtures in the unit before taking the possession. He / she / they has / have no complaint / dispute for the same. From now onwards, it is / will be Purchaser’s responsibility to keep the unit in good and tenable conditions.</li>
                            <li>49. That if the Purchaser is found to have committed breach of any of the conditions, without prejudice to the right of expulsion of the purchaser from the membership of the said service society and forfeiture of its share and maintenance deposit, the said service society shall have absolute right to compel the purchaser to restore the unit to the original position and in default, shall have a right to cause it to be done through its agents and employees at the cost of purchaser. Under such circumstances the purchaser is liable to pay penalty, charges etc. that may be fixed or decided by the service society. If the Purchaser fails to pay penalty, charges etc. then under that circumstances the purchaser is not entitled to use common facilities and common amenities of the said scheme and the same can be discontinued by service society without giving any notice and for that purchaser is not entitled to take any legal action against the service society and ultimately his / her their membership right can also be terminated.</li>
                            <li>50. The Vendor has authorized its partner_________________________________ to sign the present Sale Deed and other related documents as “Authorized Signatory”.</li>
                            <li>51. THE PURCHASER, as the context may, require, shall also include his representatives, occupiers, visitors, authorized person successors, assigns and all and every other person or person to claim under him / her / it.</li>
                            <li>52. The expression VENDOR shall also mean and include any person authorized / nominated by it or to its order the Service Society formed by the Unit holders or its assignee or transferee vested with such powers, authorities or obligations as the Vendor may think fit.</li>
                            <li>53. This Deed shall be binding on the purchaser, (in case of individual) his
                                / her / their heirs, legal representatives, executors, successors and assigns; (in case of Partnership firm) its partners as at present and from time to time and the heirs and legal representatives of the last surviving partner; (in case of HUF) its members as at present and from time to time and their respective heirs, executors and successors and its (HUF’s) permitted assigns; (in case of Trust) its Trustees as at present and from time to time and the beneficiaries thereof; (in case of Company) its present and future directors and assigns and / or any third party having or contemplating to have in future any charge or interest on the said unit and / or on the construction thereupon, in part and/or as a whole.
                                /li>
                            <li>54. The purchaser hereby declares that he / she / it / they has / have read, understood and agreed each and every term of this agreement before execution.</li>
                            <li>55. That said property is situated in peaceful area and not included under the notification of the Gujarat Prohibition of Transfer of Immoveable property and provision for protection of Tenants from Eviction from premises in Disturbed Areas Act, 1991 [Gujarat 12 of 1991]. Hence the permission under the said Act is not required for transfer of Unit.</li>
                            <li>56. That, the Vendor has paid all taxes, cesses, up to the date of scheme and if the same are found to be due or unpaid, the Vendor shall be liable to pay the same and failing which, the purchaser shall be entitled to recover them from vendor. Hereinafter purchaser is liable for payment of all type of taxes.</li>
                            <li>57. That, the expenses for stamp Duty, Registration Fees, miscellaneous expenses have been borne by the purchaser.</li>
                        </ol>
                        <p>The schedule above referred to is mentioned hereunder :</p>
                        <p style="text-align: center;"><strong><u>SCHEDULE OF PROPERTY</u></strong></p>
                        <p>

                            All That piece & parcel of Immovable property bearing <strong>Flat No. <?= $flat_name ?></strong> in <strong>Wing “ <?= $block_name ?> ” </strong> having total Carpet Area admeasuring about <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> situated on <strong><?= $floor_name ?></strong> of the said Scheme along with (i) Wash Area admeasuring <strong>wash area <?= $banakhat_details->wash_yard ?> sq.mtrs</strong>. (ii) Balcony admeasuring about <strong>balcony <?= $banakhat_details->balcony ?> sq.mtrs</strong>. (under the provisions of Gujarat RERA Act) carpet area as well as approximately <strong><?= $banakhat_details->carpet_area ?> sq.mtrs.</strong> (as per plan sanctioned by Ahmedabad Municipal Corporation) in the scheme known as “ KAUTILYA ONE-54 ” together with undivided share in the said land admeasuring about 34.17 Sq. m. bearing A) Final Plot No. 321, admeasuring 3400 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/3 admeasuring 5666 sq. m.& b) Final Plot No. 322, admeasuring 2125 sq. m. of Town Planning Scheme No. 76 / B (Chandkheda), allotted in lieu of Survey No. 875/4 admeasuring 3541 sq. m. Thereby, a total of 5525 sq. m. of land of both the final plots now amalgamated Final Plot no: (321+322) and the affordable housing project being built on the above said land situated within the village limits of Chandkheda, Taluka - Sabarmati in the Registration Sub - District of Ahmedabad - 13 (Sabarmati)

                        </p>
                        <p>The said “ KAUTILYA ONE-54 ” scheme is bounded as follows :-</p>

                        <table class="table">
                            <tr>
                                <th style="width: 180px;">On or towards East</th>
                                <td><span class="u" style="min-width: 280px;">:by <strong><u>12.00 MT. T.P. ROAD</u></strong></span></td>
                            </tr>
                            <tr>
                                <th>On or towards West</th>
                                <td><span class="u">:by <strong><u>FINAL PLOT 343 & 432</u></strong></span></td>
                            </tr>
                            <tr>
                                <th>On or towards North</th>
                                <td><span class="u">:by <strong><u>FINAL PLOT 320</u></strong></span></td>
                            </tr>
                            <tr>
                                <th>On or towards South</th>
                                <td><span class="u">:by <strong><u>FINAL PLOT 323</u></strong></span></td>
                            </tr>
                        </table>

                        <p>The said “ Unit ” is bounded as follows :-</p>
                        <table class="table">
                            <tr>
                                <th style="width: 180px;">On or towards East</th>
                                <td><span class="u" style="min-width: 280px;">:by <?= $banakhat_details->east ?></span></td>
                            </tr>
                            <tr>
                                <th>On or towards West</th>
                                <td><span class="u">:by <?= $banakhat_details->west ?></span></td>
                            </tr>
                            <tr>
                                <th>On or towards North</th>
                                <td><span class="u">:by <?= $banakhat_details->north ?></span></td>
                            </tr>
                            <tr>
                                <th>On or towards South</th>
                                <td><span class="u">:by <?= $banakhat_details->south ?></span></td>
                            </tr>
                        </table>

                        <p>IN WITNESS WHEREOF the par ties hereto have hereunto set and subscribe their respective hands hereunder on this ___ th day of _______, 2025 at Ahmedabad.</p>
                        <div class="signature-grid">
                            <div>
                                <p>SIGNED AND DEVLIVERED BY</p>
                                <div class="sign-block">THE WITHINNAMED VENDOR :-</div>
                                <p class="small muted">Kiran Rasiklal Kamdar<span class="u">________________</span></p>
                            </div>
                            <div>
                                <h3>In the presence of following two witnesses :-</h3>
                            </div>
                        </div>

                        <table class="table">
                            <tr>
                                <th style="width: 40px;">1)</th>
                                <td>Name: <span class="u">________________________</span><br />Address: <span class="u" style="min-width: 420px;">__________________________________________________________</span></td>
                            </tr>
                            <tr>
                                <th>2)</th>
                                <td>Name: <span class="u">________________________</span><br />Address: <span class="u" style="min-width: 420px;">__________________________________________________________</span></td>
                            </tr>
                        </table>

                        <div class="page-break"></div>

                        <h2>Photographs of Said Unit</h2>
                        <div class="photos">Photo Placeholder</div>

                        <h3>Postal Address of Property</h3>
                        <div class="address-block">
                            <p><strong>Flat No. <?= $flat_name ?></strong></p>
                            <p><strong>KAUTILYA ONE-54</strong></p>
                            <p>Chandkheda, Ahmedabad</p>
                        </div>

                        <div class="signature-grid">
                            <div>
                                <h3>First Party – Vendor</h3>
                                <div class="sign-block"></div>
                            </div>
                            <div>
                                <h3>Second Party – Purchaser</h3>
                                <div class="sign-block"></div>
                            </div>
                        </div>

                        <div class="page-break"></div>

                        <h2>Photographs of Said Unit</h2>
                        <div class="photos">Photo Placeholder</div>

                        <h3>Postal Address of Property</h3>
                        <div class="address-block">
                            <p><strong>Flat No. <?= $flat_name ?></strong></p>
                            <p><strong>KAUTILYA ONE-54</strong></p>
                            <p>Chandkheda, Ahmedabad</p>
                        </div>

                        <div class="signature-grid">
                            <div>
                                <h3>First Party – Vendor</h3>
                                <div class="sign-block"></div>
                            </div>
                            <div>
                                <h3>Second Party – Purchaser</h3>
                                <div class="sign-block"></div>
                            </div>
                        </div>

                        <div class="spacer"></div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>