<?
//Handler for the first task

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "SendNewReview");

function SendNewReview(&$arFields)
{
	if(CModule::IncludeModule("iblock")){
		$res = CIBlock::GetByID($arFields["IBLOCK_ID"]);
		if($arIblock = $res->GetNext()){
			if($arIblock["CODE"]=='reviews'){
				$arEventFields = array(
					"NAME_ELEMENT" => $arFields["NAME"],
					"COMMENT"      => $arFields["PROPERTY_VALUES"]["COMMENT"],
					"ADV"          => $arFields["PROPERTY_VALUES"]["ADV"],
					"DISADV"       => $arFields["PROPERTY_VALUES"]["DISADV"],
					"RATE"         => $arFields["PROPERTY_VALUES"]["RATE"],
					"HREF"         => $arFields["PROPERTY_VALUES"]["HREF"]
				);
				CEvent::Send("NEW_REVIEW", "s1", $arEventFields);
			}
		}
	}
}

//Handler for the second task

AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "SendChangeReviewBefore");

function SendChangeReviewBefore(&$arFields)
{
	if(CModule::IncludeModule("iblock")){
		$res = CIBlock::GetByID($arFields["IBLOCK_ID"]);
		if($arIblock = $res->GetNext()){
			if($arIblock["CODE"]=='reviews'){
				
				$arSelect = Array(
					"ID",
					"IBLOCK_ID",
					"NAME",
					"DATE_ACTIVE_FROM",
					"PROPERTY_HREF",
					"PROPERTY_RATE",
					"PROPERTY_ADV",
					"PROPERTY_DISADV",
					"PROPERTY_COMMENT"
				);
				$arFilter = Array(
					"IBLOCK_ID" => $arFields["IBLOCK_ID"],
					"ID"        => $arFields["ID"]
				);
				$resElem = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				if ($oldElem = $resElem->GetNext(false, false)) {
					$arEventFields = array(
						"NAME_ELEMENT" => $arFields["NAME"],
						"HREF"         => $oldElem["PROPERTY_HREF_VALUE"],
						"COMMENT_OLD"  => $oldElem["PROPERTY_COMMENT_VALUE"],
						"ADV_OLD"      => $oldElem["PROPERTY_ADV_VALUE"],
						"DISADV_OLD"   => $oldElem["PROPERTY_DISADV_VALUE"],
						"RATE_OLD"     => $oldElem["PROPERTY_RATE_VALUE"]
					);
				}

				$IBLOCK_ID = $arFields["IBLOCK_ID"];
				$properties = CIBlockProperty::GetList(Array(
					"sort" => "asc",
					"name" => "asc"
				), Array(
					"ACTIVE" => "Y",
					"IBLOCK_ID" => $IBLOCK_ID
				));
				$arTempProp = [];
				while ($propAll = $properties->GetNext(false, false)) {
					$arTempProp[] = $propAll;
					$str .= $propAll["NAME"] . ': ' . array_values($arFields["PROPERTY_VALUES"][$propAll["ID"]])[0]["VALUE"] . "<br />";
					$arEventFields["NEW_REVIEW"] = $str;
				}
				CEvent::Send("CHANGE_REVIEW", "s1", $arEventFields);
			}
		}
	}
}
